<?php

namespace Tests\Feature;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_dashboard_displays_counts_from_the_database(): void
    {
        $admin = User::factory()->administrator()->create();
        $category = VehicleCategory::factory()->create();

        TourPackage::factory()->published()->create();
        TourPackage::factory()->create();
        Vehicle::factory()->count(3)->for($category, 'category')->create();

        Inquiry::factory()->visa()->create(['status' => InquiryStatus::New]);
        Inquiry::factory()->visa()->create(['status' => InquiryStatus::Resolved]);
        Inquiry::factory()->baggage()->create(['status' => InquiryStatus::New]);
        Inquiry::factory()->create([
            'type' => InquiryType::General,
            'status' => InquiryStatus::New,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertViewHas('metrics', [
                'packages' => 2,
                'publishedPackages' => 1,
                'vehicles' => 3,
                'newInquiries' => 3,
                'visaRequests' => 2,
                'baggageRequests' => 1,
            ])
            ->assertSeeText('Total packages')
            ->assertSeeText('Published packages')
            ->assertSeeText('Recent inquiries');
    }

    public function test_dashboard_lists_only_the_eight_most_recent_inquiries(): void
    {
        $admin = User::factory()->owner()->create();
        $oldest = Inquiry::factory()->create([
            'name' => 'Oldest Customer',
            'created_at' => now()->subDays(10),
        ]);

        Inquiry::factory()->count(8)->sequence(
            fn ($sequence): array => [
                'name' => 'Recent Customer '.($sequence->index + 1),
                'created_at' => now()->subMinutes(8 - $sequence->index),
            ],
        )->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $recentInquiries = $response->viewData('recentInquiries');

        $response->assertOk()->assertDontSeeText($oldest->name);
        $this->assertCount(8, $recentInquiries);
        $this->assertSame('Recent Customer 8', $recentInquiries->first()->name);
        $this->assertTrue($recentInquiries->every->relationLoaded('assignedUser'));
    }

    public function test_dashboard_layout_contains_all_navigation_and_mobile_menu(): void
    {
        $admin = User::factory()->editor()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Mobile admin navigation')
            ->assertSeeTextInOrder([
                'Dashboard', 'Packages', 'Vehicles', 'Villas & Houses', 'Inquiries', 'Visa', 'Baggage',
                'Gallery', 'Blog', 'Pages', 'SEO', 'Redirects', 'Settings', 'Audit Logs',
            ]);
    }

    public function test_admin_module_routes_remain_protected(): void
    {
        $this->get(route('admin.packages.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }
}
