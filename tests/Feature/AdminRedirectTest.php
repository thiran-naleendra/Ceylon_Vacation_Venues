<?php

namespace Tests\Feature;

use App\Models\Redirect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_owner_can_manage_a_redirect_and_it_resolves_publicly(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('admin.redirects.store'), [
            'source_path' => '/old-package',
            'destination_path' => '/tour-packages/new-package',
            'status_code' => 301,
            'is_active' => '1',
        ])->assertRedirect(route('admin.redirects.index'));

        $redirect = Redirect::query()->sole();
        $this->get('/old-package')->assertStatus(301)->assertRedirect('/tour-packages/new-package');

        $this->actingAs($owner)->put(route('admin.redirects.update', $redirect), [
            'source_path' => '/former-package',
            'destination_path' => '/tour-packages/new-package',
            'status_code' => 308,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('redirects', ['source_path' => '/former-package', 'status_code' => 308]);
        $this->actingAs($owner)->delete(route('admin.redirects.destroy', $redirect))->assertRedirect();
        $this->assertDatabaseMissing('redirects', ['id' => $redirect->id]);
    }

    public function test_redirect_validation_rejects_external_self_and_unsafe_paths(): void
    {
        $owner = User::factory()->owner()->create();

        foreach ([
            ['source_path' => '/old', 'destination_path' => 'https://example.com'],
            ['source_path' => '/same', 'destination_path' => '/same'],
            ['source_path' => '/unsafe?query=yes', 'destination_path' => '/safe'],
        ] as $paths) {
            $this->actingAs($owner)->post(route('admin.redirects.store'), [
                ...$paths,
                'status_code' => 301,
                'is_active' => '1',
            ])->assertSessionHasErrors();
        }

        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_editor_can_view_redirects_but_cannot_mutate_them(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get(route('admin.redirects.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.redirects.create'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.redirects.store'), [
            'source_path' => '/old',
            'destination_path' => '/new',
            'status_code' => 301,
            'is_active' => '1',
        ])->assertForbidden();
    }
}
