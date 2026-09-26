<?php

namespace App\Http\Requests\Admin\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:254', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in($this->assignableRoles())],
            'is_active' => ['required', 'boolean'],
            'email_verified' => ['required', 'boolean'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ];
    }

    /** @return array<int, string> */
    protected function assignableRoles(): array
    {
        $roles = [UserRole::Administrator, UserRole::Editor, UserRole::InquiryAgent];
        if ($this->user()?->role === UserRole::Owner) {
            array_unshift($roles, UserRole::Owner);
        }

        return array_map(fn (UserRole $role): string => $role->value, $roles);
    }
}
