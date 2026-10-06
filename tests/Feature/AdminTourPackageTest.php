<?php

namespace Tests\Feature;

use App\Enums\ImageProcessingStatus;
use App\Enums\InquiryType;
use App\Enums\PublicationStatus;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\PackageImage;
use App\Models\PackageInquiryDetail;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTourPackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_editor_can_create_a_package_with_itinerary_image_and_seo(): void
    {
        $editor = User::factory()->editor()->create();

        $response = $this->actingAs($editor)->post(route('admin.packages.store'), [
            ...$this->validPackageData(),
            'featured_image' => UploadedFile::fake()->image('sigiriya.jpg', 1200, 800),
            'featured_image_alt' => 'Sigiriya rock fortress above the forest',
        ]);

        $package = TourPackage::query()->sole();
        $response->assertRedirect(route('admin.packages.show', $package));

        $this->assertSame('cultural-triangle-adventure', $package->slug);
        $this->assertSame(PublicationStatus::Draft, $package->status);
        $this->assertFalse($package->is_featured);
        $this->assertCount(2, $package->itineraries);
        $this->assertSame([1, 2], $package->itineraries()->orderBy('sort_order')->pluck('day_number')->all());

        $image = $package->images()->sole();
        $this->assertSame('image/webp', $image->mime_type);
        $this->assertSame(ImageProcessingStatus::Ready, $image->processing_status);
        $this->assertSame('Sigiriya rock fortress above the forest', $image->alt_text);
        Storage::disk('public')->assertExists($image->path);
        Storage::disk('public')->assertExists($image->variants['small']['path']);
        Storage::disk('public')->assertExists($image->variants['medium']['path']);

        $this->assertSame('Cultural Triangle Tour', $package->seoMetadata->meta_title);
        $this->assertSame($image->path, $package->seoMetadata->og_image_path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'package.created', 'subject_id' => $package->id]);
    }

    public function test_package_input_and_images_are_strictly_validated(): void
    {
        $editor = User::factory()->editor()->create();

        $response = $this->actingAs($editor)->post(route('admin.packages.store'), [
            ...$this->validPackageData(),
            'duration_days' => 0,
            'currency' => 'US',
            'featured_image' => UploadedFile::fake()->create('payload.svg', 10, 'image/svg+xml'),
            'featured_image_alt' => '',
        ]);

        $response->assertSessionHasErrors(['duration_days', 'currency', 'featured_image', 'featured_image_alt']);
        $this->assertDatabaseEmpty('tour_packages');
    }

    public function test_package_listing_searches_and_filters_real_records(): void
    {
        $editor = User::factory()->editor()->create();
        TourPackage::factory()->create(['title' => 'Ella Highlands', 'destination' => 'Ella']);
        TourPackage::factory()->published()->create(['title' => 'Galle Coast', 'destination' => 'Galle']);

        $this->actingAs($editor)->get(route('admin.packages.index', [
            'search' => 'Galle',
            'status' => 'published',
        ]))->assertOk()
            ->assertSeeText('Galle Coast')
            ->assertDontSeeText('Ella Highlands');
    }

    public function test_update_reorders_itinerary_and_rejects_images_from_another_package(): void
    {
        $editor = User::factory()->editor()->create();
        $package = TourPackage::factory()->create();
        $otherPackage = TourPackage::factory()->create();
        $otherImage = PackageImage::factory()->for($otherPackage)->create();

        $response = $this->actingAs($editor)->put(route('admin.packages.update', $package), [
            ...$this->validPackageData(),
            'delete_image_ids' => [$otherImage->id],
        ]);

        $response->assertSessionHasErrors('delete_image_ids.0');
        $this->assertDatabaseHas('package_images', ['id' => $otherImage->id, 'tour_package_id' => $otherPackage->id]);

        $this->actingAs($editor)->put(route('admin.packages.update', $package), [
            ...$this->validPackageData(),
            'itineraries' => [
                ['day_number' => 2, 'title' => 'Second day first', 'description' => 'Reordered'],
                ['day_number' => 1, 'title' => 'First day second', 'description' => 'Reordered'],
            ],
        ])->assertRedirect(route('admin.packages.show', $package));

        $this->assertSame([2, 1], $package->itineraries()->orderBy('sort_order')->pluck('day_number')->all());
    }

    public function test_publishing_requires_images_with_alt_text_and_records_status_changes(): void
    {
        $editor = User::factory()->editor()->create();
        $package = TourPackage::factory()->create();

        $this->actingAs($editor)->patch(route('admin.packages.status', $package), [
            'status' => 'published',
        ])->assertSessionHasErrors('status');

        PackageImage::factory()->for($package)->create(['alt_text' => 'Tea hills in Sri Lanka']);

        $this->actingAs($editor)->patch(route('admin.packages.status', $package), [
            'status' => 'published',
            'is_featured' => true,
        ])->assertRedirect();

        $package->refresh();
        $this->assertSame(PublicationStatus::Published, $package->status);
        $this->assertTrue($package->is_featured);
        $this->assertNotNull($package->published_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'package.status_updated', 'subject_id' => $package->id]);
    }

    public function test_inquiry_agents_cannot_manage_packages_and_editors_cannot_delete_them(): void
    {
        $package = TourPackage::factory()->create();

        $this->actingAs(User::factory()->inquiryAgent()->create())
            ->get(route('admin.packages.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->editor()->create())
            ->delete(route('admin.packages.destroy', $package))
            ->assertForbidden();
    }

    public function test_delete_action_is_only_shown_to_users_authorized_to_delete_packages(): void
    {
        $package = TourPackage::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.packages.index'))
            ->assertOk()
            ->assertSee('action="'.route('admin.packages.destroy', $package).'"', false)
            ->assertSeeText('Delete');

        $this->actingAs(User::factory()->editor()->create())
            ->get(route('admin.packages.index'))
            ->assertOk()
            ->assertDontSee('action="'.route('admin.packages.destroy', $package).'"', false)
            ->assertDontSeeText('Delete');
    }

    public function test_deletion_is_blocked_for_inquiry_history_and_removes_files_otherwise(): void
    {
        $administrator = User::factory()->administrator()->create();
        $protectedPackage = TourPackage::factory()->create();
        $inquiry = Inquiry::factory()->package()->create(['type' => InquiryType::Package]);
        PackageInquiryDetail::factory()->for($inquiry)->for($protectedPackage, 'tourPackage')->create();

        $this->actingAs($administrator)
            ->delete(route('admin.packages.destroy', $protectedPackage))
            ->assertSessionHasErrors('package');

        $deletablePackage = TourPackage::factory()->create();
        $image = PackageImage::factory()->for($deletablePackage)->create([
            'path' => 'packages/test/image.webp',
            'variants' => ['small' => ['path' => 'packages/test/image-480.webp', 'width' => 480, 'height' => 320]],
        ]);
        Storage::disk('public')->put($image->path, 'image');
        Storage::disk('public')->put($image->variants['small']['path'], 'image');

        $this->actingAs($administrator)
            ->delete(route('admin.packages.destroy', $deletablePackage))
            ->assertRedirect(route('admin.packages.index'));

        $this->assertDatabaseMissing('tour_packages', ['id' => $deletablePackage->id]);
        Storage::disk('public')->assertMissing($image->path);
        Storage::disk('public')->assertMissing($image->variants['small']['path']);
        $this->assertSame('package.deleted', AuditLog::query()->latest('id')->value('action'));
    }

    /** @return array<string, mixed> */
    private function validPackageData(): array
    {
        return [
            'title' => 'Cultural Triangle Adventure',
            'slug' => '',
            'summary' => 'Explore Sri Lanka’s ancient cultural heart.',
            'description' => 'A carefully planned journey through the Cultural Triangle.',
            'destination' => 'Sigiriya, Dambulla and Polonnaruwa',
            'availability_status' => 'available',
            'duration_days' => 5,
            'duration_nights' => 4,
            'starting_price' => '650.00',
            'currency' => 'usd',
            'price_basis' => 'per_person',
            'sort_order' => 1,
            'itineraries' => [
                ['day_number' => 1, 'title' => 'Arrival in Sigiriya', 'description' => 'Hotel transfer and rest.'],
                ['day_number' => 2, 'title' => 'Sigiriya fortress', 'description' => 'Morning climb and village tour.'],
            ],
            'seo' => [
                'meta_title' => 'Cultural Triangle Tour',
                'meta_description' => 'Explore Sri Lanka’s Cultural Triangle.',
                'canonical_url' => 'https://ceylonvacationvenues.com/tour-packages/cultural-triangle-adventure',
                'robots_index' => '1',
                'robots_follow' => '1',
                'og_title' => 'Cultural Triangle Adventure',
                'og_description' => 'An unforgettable Sri Lankan heritage tour.',
                'og_image_alt' => 'Sigiriya rock fortress',
            ],
            'seo_use_featured_image' => '1',
        ];
    }
}
