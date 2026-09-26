<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        RateLimiter::clear('admin@example.com|127.0.0.1');
    }

    public function test_admin_login_page_is_available_and_registration_is_not_public(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Secure admin portal')
            ->assertSee('noindex, nofollow', false);

        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_guests_are_redirected_from_admin_pages(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_active_admin_can_login_and_session_is_regenerated(): void
    {
        $admin = User::factory()->administrator()->create([
            'email' => 'admin@example.com',
            'password' => 'a-secure-password',
        ]);

        $this->app['session']->start();
        $previousSessionId = $this->app['session']->getId();

        $this->post(route('admin.login.store'), [
            'email' => 'ADMIN@example.com',
            'password' => 'a-secure-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertNotSame($previousSessionId, $this->app['session']->getId());
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_inactive_and_non_admin_accounts_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'visitor@example.com',
            'password' => 'correct-password',
        ]);
        User::factory()->administrator()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => 'correct-password',
        ]);

        foreach (['visitor@example.com', 'inactive@example.com'] as $email) {
            $this->post(route('admin.login.store'), [
                'email' => $email,
                'password' => 'correct-password',
            ])->assertSessionHasErrors('email');

            $this->assertGuest();
        }
    }

    public function test_non_admin_and_inactive_sessions_cannot_access_admin_pages(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->assertGuest();

        $this->actingAs(User::factory()->owner()->inactive()->create())
            ->post(route('admin.logout'))
            ->assertForbidden();

        $this->assertGuest();
    }

    public function test_login_attempts_are_throttled(): void
    {
        User::factory()->administrator()->create([
            'email' => 'admin@example.com',
            'password' => 'correct-password',
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('admin.login.store'), [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'correct-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_clears_authentication_and_protected_session_data(): void
    {
        $admin = User::factory()->owner()->create();

        $this->actingAs($admin)
            ->withSession(['sensitive-admin-state' => 'present'])
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionMissing('sensitive-admin-state');

        $this->assertGuest();
    }

    public function test_roles_expose_the_expected_authorization_foundation(): void
    {
        $owner = User::factory()->owner()->create();
        $editor = User::factory()->editor()->create();
        $agent = User::factory()->inquiryAgent()->create();

        $this->assertTrue(Gate::forUser($owner)->allows('manage-admin-users'));
        $this->assertTrue(Gate::forUser($editor)->allows('publish-content'));
        $this->assertFalse(Gate::forUser($editor)->allows('manage-inquiries'));
        $this->assertTrue(Gate::forUser($agent)->allows('manage-inquiries'));
        $this->assertFalse(Gate::forUser($agent)->allows('publish-content'));

        $this->assertTrue($owner->can('update', $editor));
        $this->assertFalse($editor->can('update', $owner));
        $this->assertFalse($owner->can('delete', $owner));
    }

    public function test_admin_role_cannot_be_mass_assigned(): void
    {
        $user = new User;
        $user->fill([
            'name' => 'Attempted Admin',
            'email' => 'attempt@example.com',
            'password' => 'password',
            'role' => UserRole::Owner,
            'is_active' => false,
        ]);

        $this->assertSame(UserRole::None, $user->role);
        $this->assertTrue($user->is_active);
    }
}
