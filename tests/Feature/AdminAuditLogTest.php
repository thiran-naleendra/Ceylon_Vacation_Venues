<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_owner_and_administrator_can_list_and_view_audit_entries(): void
    {
        $actor = User::factory()->editor()->create(['name' => 'Content Editor', 'email' => 'editor@example.com']);
        $log = AuditLog::factory()->for($actor, 'actor')->create([
            'action' => 'admin.page.updated', 'subject_type' => 'App\\Models\\Page', 'subject_id' => 12,
            'subject_label' => 'About Sri Lanka', 'request_id' => '11111111-1111-4111-8111-111111111111',
        ]);

        foreach ([User::factory()->owner()->create(), User::factory()->administrator()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.audit-logs.index'))->assertOk()
                ->assertSeeText('About Sri Lanka')->assertSeeText('Content Editor')->assertSeeText('Admin Page Updated');
            $this->get(route('admin.audit-logs.show', $log))->assertOk()
                ->assertSeeText('editor@example.com')->assertSeeText('11111111-1111-4111-8111-111111111111');
        }
    }

    public function test_all_admin_roles_can_read_logs_while_guests_and_non_admins_are_blocked(): void
    {
        $log = AuditLog::factory()->create();
        foreach ([User::factory()->editor()->create(), User::factory()->inquiryAgent()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.audit-logs.index'))->assertOk();
            $this->get(route('admin.audit-logs.show', $log))->assertOk();
            $this->get(route('admin.dashboard'))->assertOk()->assertSee(route('admin.audit-logs.index'), false);
        }
        auth()->logout();
        $this->get(route('admin.audit-logs.index'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create())->get(route('admin.audit-logs.index'))->assertForbidden();
    }

    public function test_search_filters_dates_and_pagination_use_real_audit_data(): void
    {
        $owner = User::factory()->owner()->create();
        $actor = User::factory()->administrator()->create(['name' => 'Operations Admin']);
        AuditLog::factory()->for($actor, 'actor')->create([
            'action' => 'user.activated', 'subject_type' => User::class, 'subject_label' => 'Target User', 'created_at' => '2026-09-20 10:00:00',
        ]);
        AuditLog::factory()->create([
            'action' => 'page.updated', 'subject_type' => 'App\\Models\\Page', 'subject_label' => 'Privacy Policy', 'created_at' => '2026-08-01 10:00:00',
        ]);
        AuditLog::factory()->count(31)->create(['action' => 'content.updated']);

        $this->actingAs($owner)->get(route('admin.audit-logs.index', [
            'search' => 'Operations', 'action' => 'user.activated', 'actor' => $actor->id,
            'subject_type' => User::class, 'date_from' => '2026-09-20', 'date_to' => '2026-09-20',
        ]))->assertOk()->assertSeeText('Target User')->assertDontSeeText('Privacy Policy');

        $this->get(route('admin.audit-logs.index', ['action' => 'content.updated']))
            ->assertOk()->assertSee('action=content.updated&amp;page=2', false);
    }

    public function test_detail_escapes_payloads_and_records_remain_append_only(): void
    {
        $owner = User::factory()->owner()->create();
        $log = AuditLog::factory()->create([
            'subject_label' => '<script>alert(1)</script>',
            'changes' => ['title' => ['old' => 'Safe', 'new' => '<img src=x onerror=alert(1)>']],
            'metadata' => ['changed_fields' => ['title']],
        ]);

        $this->actingAs($owner)->get(route('admin.audit-logs.show', $log))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);

        $this->expectException(LogicException::class);
        $log->forceFill(['action' => 'tampered'])->save();
    }

    public function test_audit_log_routes_are_read_only(): void
    {
        $owner = User::factory()->owner()->create();
        $log = AuditLog::factory()->create();
        $this->actingAs($owner)->post('/admin/audit-logs', [])->assertMethodNotAllowed();
        $this->put('/admin/audit-logs/'.$log->id, ['action' => 'tampered'])->assertMethodNotAllowed();
        $this->delete('/admin/audit-logs/'.$log->id)->assertMethodNotAllowed();
        $this->assertDatabaseHas('audit_logs', ['id' => $log->id, 'action' => $log->action]);
    }
}
