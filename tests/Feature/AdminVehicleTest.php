<?php

namespace Tests\Feature;

use App\Enums\ImageProcessingStatus;
use App\Enums\PublicationStatus;
use App\Enums\VehicleAvailability;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminVehicleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_editor_can_create_vehicle_with_image_and_seo(): void
    {
        $category = VehicleCategory::factory()->published()->create();
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.vehicles.store'), [
            ...$this->validData($category),
            'featured_image' => UploadedFile::fake()->image('car.jpg', 1200, 800),
            'featured_image_alt' => 'Blue rental car beside a Sri Lankan beach',
        ]);

        $vehicle = Vehicle::query()->sole();
        $response->assertRedirect(route('admin.vehicles.show', $vehicle));
        $this->assertSame('premium-sedan', $vehicle->slug);
        $this->assertSame(VehicleAvailability::Available, $vehicle->availability_status);
        $this->assertTrue($vehicle->has_air_conditioning);
        $this->assertSame(PublicationStatus::Draft, $vehicle->status);
        $image = $vehicle->images()->sole();
        $this->assertSame(ImageProcessingStatus::Ready, $image->processing_status);
        Storage::disk('public')->assertExists($image->path);
        $this->assertSame('Premium Sedan Rental', $vehicle->seoMetadata->meta_title);
        $this->assertDatabaseHas('audit_logs', ['action' => 'vehicle.created', 'subject_id' => $vehicle->id]);
    }

    public function test_vehicle_input_and_images_are_strictly_validated(): void
    {
        $category = VehicleCategory::factory()->create();
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.vehicles.store'), [
            ...$this->validData($category),
            'vehicle_category_id' => 999999,
            'currency' => 'US',
            'featured_image' => UploadedFile::fake()->create('payload.svg', 10, 'image/svg+xml'),
            'featured_image_alt' => '',
        ]);

        $response->assertSessionHasErrors(['vehicle_category_id', 'currency', 'featured_image', 'featured_image_alt']);
        $this->assertDatabaseEmpty('vehicles');
    }

    public function test_listing_searches_and_filters_real_vehicle_records(): void
    {
        $car = VehicleCategory::factory()->create(['name' => 'Car']);
        $bike = VehicleCategory::factory()->create(['name' => 'Bike']);
        Vehicle::factory()->for($car, 'category')->published()->create(['title' => 'Coastal Sedan']);
        Vehicle::factory()->for($bike, 'category')->create(['title' => 'City Scooter']);

        $this->actingAs(User::factory()->editor()->create())->get(route('admin.vehicles.index', [
            'search' => 'Coastal', 'category' => $car->id, 'status' => 'published',
        ]))->assertOk()->assertSeeText('Coastal Sedan')->assertDontSeeText('City Scooter');
    }

    public function test_update_rejects_an_image_owned_by_another_vehicle(): void
    {
        $editor = User::factory()->editor()->create();
        $vehicle = Vehicle::factory()->create();
        $otherImage = VehicleImage::factory()->for(Vehicle::factory())->create();

        $this->actingAs($editor)->put(route('admin.vehicles.update', $vehicle), [
            ...$this->validData($vehicle->category), 'delete_image_ids' => [$otherImage->id],
        ])->assertSessionHasErrors('delete_image_ids.0');

        $this->assertDatabaseHas('vehicle_images', ['id' => $otherImage->id]);
    }

    public function test_publishing_requires_an_image_with_alt_text(): void
    {
        $editor = User::factory()->editor()->create();
        $vehicle = Vehicle::factory()->create();
        $this->actingAs($editor)->patch(route('admin.vehicles.status', $vehicle), ['status' => 'published'])->assertSessionHasErrors('status');
        VehicleImage::factory()->for($vehicle)->create(['alt_text' => 'White tuk tuk in Colombo']);

        $this->actingAs($editor)->patch(route('admin.vehicles.status', $vehicle), ['status' => 'published', 'is_featured' => true])->assertRedirect();
        $vehicle->refresh();
        $this->assertSame(PublicationStatus::Published, $vehicle->status);
        $this->assertTrue($vehicle->is_featured);
    }

    public function test_permissions_protect_vehicle_management_and_category_deletion(): void
    {
        $vehicle = Vehicle::factory()->create();
        $this->actingAs(User::factory()->inquiryAgent()->create())->get(route('admin.vehicles.index'))->assertForbidden();
        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.vehicles.destroy', $vehicle))->assertForbidden();
        $this->actingAs(User::factory()->administrator()->create())->delete(route('admin.vehicle-categories.destroy', $vehicle->category))->assertSessionHasErrors('category');
    }

    public function test_administrator_can_manage_vehicle_categories(): void
    {
        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin)->post(route('admin.vehicle-categories.store'), [
            'name' => 'Luxury Van', 'slug' => '', 'description' => 'Premium group transport.', 'status' => 'published', 'sort_order' => 4,
        ])->assertRedirect(route('admin.vehicle-categories.index'));

        $category = VehicleCategory::query()->where('name', 'Luxury Van')->sole();
        $this->assertSame('luxury-van', $category->slug);
        $this->assertSame(PublicationStatus::Published, $category->status);
        $this->assertNotNull($category->published_at);
    }

    /** @return array<string, mixed> */
    private function validData(VehicleCategory $category): array
    {
        return [
            'vehicle_category_id' => $category->id,
            'title' => 'Premium Sedan', 'slug' => '',
            'summary' => 'Comfortable private transport.', 'description' => 'A modern vehicle for island travel.',
            'rental_rate' => '75.50', 'currency' => 'usd', 'rate_unit' => 'day', 'transmission' => 'automatic',
            'seats' => 4, 'luggage_capacity' => 2, 'has_air_conditioning' => '1',
            'availability_status' => 'available', 'sort_order' => 1,
            'seo' => ['meta_title' => 'Premium Sedan Rental', 'meta_description' => 'Rent a sedan in Sri Lanka.', 'canonical_url' => 'https://ceylonvacationvenues.com/vehicles/premium-sedan', 'robots_index' => '1', 'robots_follow' => '1', 'og_title' => 'Premium Sedan', 'og_description' => 'Comfortable transport.', 'og_image_alt' => 'Premium sedan'],
            'seo_use_featured_image' => '1',
        ];
    }
}
