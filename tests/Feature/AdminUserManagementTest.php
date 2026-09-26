<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_owner_can_create_view_search_filter_update_and_delete_users(): void
    {
        $owner = User::factory()->owner()->create();
        $response = $this->actingAs($owner)->post(route('admin.users.store'), $this->validData([
            'name' => 'Content Editor', 'email' => 'editor@example.com', 'role' => UserRole::Editor->value,
        ]));

        $managedUser = User::query()->where('email', 'editor@example.com')->sole();
        $response->assertRedirect(route('admin.users.show', $managedUser));
        $this->assertTrue(Hash::check('Strong!Pass123', $managedUser->password));
        $this->assertNotNull($managedUser->email_verified_at);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $owner->id, 'action' => 'user.created', 'subject_id' => $managedUser->id]);

        $this->get(route('admin.users.index', ['search' => 'Content', 'role' => 'editor', 'status' => 'active']))
            ->assertOk()->assertSeeText('Content Editor');
        $this->get(route('admin.users.show', $managedUser))->assertOk()->assertSeeText('Editor');

        $oldPassword = $managedUser->password;
        $this->put(route('admin.users.update', $managedUser), $this->validData([
            'name' => 'Inquiry Manager', 'email' => 'manager@example.com', 'role' => UserRole::InquiryAgent->value,
            'is_active' => '0', 'email_verified' => '0', 'password' => '', 'password_confirmation' => '',
        ]))->assertRedirect(route('admin.users.show', $managedUser));
        $managedUser->refresh();
        $this->assertSame($oldPassword, $managedUser->password);
        $this->assertSame(UserRole::InquiryAgent, $managedUser->role);
        $this->assertFalse($managedUser->is_active);
        $this->assertNull($managedUser->email_verified_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.role_changed', 'subject_id' => $managedUser->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated', 'subject_id' => $managedUser->id]);

        $this->delete(route('admin.users.destroy', $managedUser))->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $managedUser->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deleted', 'subject_id' => $managedUser->id]);
    }

    public function test_administrator_can_manage_non_owners_but_cannot_view_change_or_assign_owner(): void
    {
        $owner = User::factory()->owner()->create();
        $administrator = User::factory()->administrator()->create();
        $editor = User::factory()->editor()->create();

        $this->actingAs($administrator)->get(route('admin.users.index'))->assertOk()->assertSeeText($editor->name)->assertDontSeeText($owner->email);
        $this->get(route('admin.users.show', $owner))->assertForbidden();
        $this->get(route('admin.users.edit', $owner))->assertForbidden();
        $this->put(route('admin.users.update', $owner), $this->validData(['role' => UserRole::Administrator->value]))->assertForbidden();
        $this->delete(route('admin.users.destroy', $owner))->assertForbidden();

        $this->post(route('admin.users.store'), $this->validData(['email' => 'escalation@example.com', 'role' => UserRole::Owner->value]))
            ->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'escalation@example.com']);

        $this->put(route('admin.users.update', $editor), $this->validData(['email' => $editor->email, 'role' => UserRole::Owner->value, 'password' => '', 'password_confirmation' => '']))
            ->assertSessionHasErrors('role');
        $this->assertSame(UserRole::Editor, $editor->refresh()->role);
    }

    public function test_editors_agents_and_guests_cannot_access_user_management(): void
    {
        $target = User::factory()->editor()->create();
        foreach ([User::factory()->editor()->create(), User::factory()->inquiryAgent()->create()] as $actor) {
            $this->actingAs($actor)->get(route('admin.users.index'))->assertForbidden();
            $this->get(route('admin.users.create'))->assertForbidden();
            $this->get(route('admin.users.show', $target))->assertForbidden();
            $this->put(route('admin.users.update', $target), $this->validData(['email' => $target->email]))->assertForbidden();
            $this->delete(route('admin.users.destroy', $target))->assertForbidden();
        }
        auth()->logout();
        $this->get(route('admin.users.index'))->assertRedirect(route('admin.login'));
    }

    public function test_last_active_owner_and_self_account_are_protected(): void
    {
        $owner = User::factory()->owner()->create();
        $this->actingAs($owner)->put(route('admin.users.update', $owner), $this->validData([
            'email' => $owner->email, 'role' => UserRole::Administrator->value, 'password' => '', 'password_confirmation' => '',
        ]))->assertSessionHasErrors('role');
        $this->assertSame(UserRole::Owner, $owner->refresh()->role);

        $this->put(route('admin.users.update', $owner), $this->validData([
            'email' => $owner->email, 'is_active' => '0', 'password' => '', 'password_confirmation' => '',
        ]))->assertSessionHasErrors('is_active');
        $this->assertTrue($owner->refresh()->is_active);
        $this->delete(route('admin.users.destroy', $owner))->assertForbidden();
    }

    public function test_password_update_is_hashed_and_never_written_to_audit_payloads(): void
    {
        $owner = User::factory()->owner()->create();
        $user = User::factory()->editor()->create();
        $this->actingAs($owner)->put(route('admin.users.update', $user), $this->validData([
            'email' => $user->email, 'role' => UserRole::Editor->value,
            'password' => 'New!SecurePass456', 'password_confirmation' => 'New!SecurePass456',
        ]))->assertRedirect(route('admin.users.show', $user));

        $this->assertTrue(Hash::check('New!SecurePass456', $user->refresh()->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.password_changed', 'subject_id' => $user->id]);
        $payload = AuditLog::query()->where('subject_type', User::class)->where('subject_id', $user->id)->get()->toJson();
        $this->assertStringNotContainsString('New!SecurePass456', $payload);
        $this->assertStringNotContainsString($user->password, $payload);
    }

    public function test_duplicate_email_and_weak_or_unconfirmed_password_are_rejected(): void
    {
        $owner = User::factory()->owner()->create();
        $existing = User::factory()->editor()->create(['email' => 'duplicate@example.com']);
        $this->actingAs($owner)->post(route('admin.users.store'), $this->validData([
            'email' => strtoupper($existing->email), 'password' => 'weak', 'password_confirmation' => 'different',
        ]))->assertSessionHasErrors(['email', 'password']);
    }

    public function test_user_listing_is_paginated_and_preserves_filters(): void
    {
        $owner = User::factory()->owner()->create();
        User::factory()->editor()->count(21)->create();

        $this->actingAs($owner)->get(route('admin.users.index', ['role' => 'editor']))
            ->assertOk()
            ->assertSee('role=editor&amp;page=2', false);
    }

    public function test_inactive_user_cannot_log_in_or_continue_using_admin_routes(): void
    {
        $user = User::factory()->administrator()->create(['email' => 'inactive-admin@example.com', 'password' => 'Strong!Pass123']);
        $user->forceFill(['is_active' => false])->save();

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'Strong!Pass123'])->assertSessionHasErrors('email');
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->assertGuest();
    }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function validData(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Admin User', 'email' => 'admin-user@example.com', 'role' => UserRole::Administrator->value,
            'is_active' => '1', 'email_verified' => '1', 'password' => 'Strong!Pass123', 'password_confirmation' => 'Strong!Pass123',
        ], $overrides);
    }
}
