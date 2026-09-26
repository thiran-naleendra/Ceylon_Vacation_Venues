<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\IndexUserRequest;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(IndexUserRequest $request): View
    {
        $filters = $request->validated();
        $users = User::query()
            ->when($request->user()->role === UserRole::Administrator, fn ($query) => $query->where('role', '!=', UserRole::Owner->value))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users, 'filters' => $filters, 'roles' => $this->assignableRoles($request->user())]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return $this->form(new User, 'admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($request, $data): User {
            $user = new User;
            $user->fill([
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'password' => Hash::make($data['password']),
            ]);
            $user->forceFill([
                'role' => UserRole::from($data['role']),
                'is_active' => (bool) $data['is_active'],
                'email_verified_at' => $data['email_verified'] ? now() : null,
            ])->save();
            $this->audit($request->user(), $user, 'user.created', metadata: ['role' => $user->role->value, 'is_active' => $user->is_active]);

            return $user;
        });

        return redirect()->route('admin.users.show', $user)->with('success', 'User created.');
    }

    public function show(User $user): View
    {
        Gate::authorize('view', $user);
        $user->loadCount(['assignedInquiries', 'inquiryNotes', 'blogPosts', 'auditLogs']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return $this->form($user, 'admin.users.edit');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($request, $data, $user): void {
            $managedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            Gate::forUser($request->user())->authorize('update', $managedUser);
            $oldRole = $managedUser->role;
            $oldActive = $managedUser->is_active;
            $oldVerified = $managedUser->email_verified_at !== null;
            $newRole = UserRole::from($data['role']);
            $newActive = (bool) $data['is_active'];
            $this->protectLastActiveOwner($managedUser, $newRole, $newActive);

            $managedUser->fill(['name' => $data['name'], 'email' => Str::lower($data['email'])]);
            $managedUser->forceFill([
                'role' => $newRole,
                'is_active' => $newActive,
                'email_verified_at' => $data['email_verified'] ? ($managedUser->email_verified_at ?? now()) : null,
            ]);
            if (filled($data['password'] ?? null)) {
                $managedUser->password = Hash::make($data['password']);
                $managedUser->remember_token = null;
            }
            $managedUser->save();

            $actor = $request->user();
            $this->audit($actor, $managedUser, 'user.updated', metadata: ['changed_fields' => $this->safeChangedFields($managedUser)]);
            if ($oldRole !== $newRole) {
                $this->audit($actor, $managedUser, 'user.role_changed', ['from' => $oldRole->value, 'to' => $newRole->value]);
            }
            if ($oldActive !== $newActive) {
                $this->audit($actor, $managedUser, $newActive ? 'user.activated' : 'user.deactivated');
            }
            if (filled($data['password'] ?? null)) {
                $this->audit($actor, $managedUser, 'user.password_changed');
            }
            if ($oldVerified !== ($managedUser->email_verified_at !== null)) {
                $this->audit($actor, $managedUser, 'user.email_verification_changed');
            }
        });

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);
        DB::transaction(function () use ($user): void {
            $managedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            Gate::forUser(request()->user())->authorize('delete', $managedUser);
            $this->protectLastActiveOwner($managedUser, UserRole::None, false);
            $this->audit(request()->user(), $managedUser, 'user.deleted', metadata: ['role' => $managedUser->role->value]);
            $managedUser->delete();
        });

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    private function form(User $user, string $view): View
    {
        return view($view, ['managedUser' => $user, 'roles' => $this->assignableRoles(request()->user())]);
    }

    /** @return array<int, UserRole> */
    private function assignableRoles(User $actor): array
    {
        $roles = [UserRole::Administrator, UserRole::Editor, UserRole::InquiryAgent];
        if ($actor->role === UserRole::Owner) {
            array_unshift($roles, UserRole::Owner);
        }

        return $roles;
    }

    private function protectLastActiveOwner(User $user, UserRole $newRole, bool $newActive): void
    {
        if ($user->role !== UserRole::Owner || ! $user->is_active || ($newRole === UserRole::Owner && $newActive)) {
            return;
        }

        $activeOwners = User::query()->where('role', UserRole::Owner->value)->where('is_active', true)->lockForUpdate()->get(['id'])->count();
        if ($activeOwners <= 1) {
            throw ValidationException::withMessages(['role' => 'The last active owner cannot be deactivated, demoted, or deleted.']);
        }
    }

    /** @return array<int, string> */
    private function safeChangedFields(User $user): array
    {
        return collect(array_keys($user->getChanges()))
            ->reject(fn (string $field): bool => in_array($field, ['password', 'remember_token', 'updated_at'], true))
            ->values()
            ->all();
    }

    /** @param array<string, mixed>|null $changes @param array<string, mixed>|null $metadata */
    private function audit(User $actor, User $subject, string $action, ?array $changes = null, ?array $metadata = null): void
    {
        (new AuditLog)->forceFill([
            'actor_id' => $actor->getKey(),
            'action' => $action,
            'subject_type' => User::class,
            'subject_id' => $subject->getKey(),
            'subject_label' => $subject->name,
            'changes' => $changes,
            'metadata' => $metadata,
            'request_id' => (string) Str::uuid(),
        ])->save();
    }
}
