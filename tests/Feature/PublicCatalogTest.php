<?php

namespace Tests\Feature;

use App\Enums\PackageAvailability;
use App\Enums\VehicleAvailability;
use App\Models\PackageImage;
use App\Models\PackageItinerary;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_package_catalog_filters_published_records_and_preserves_pagination_query(): void
    {
        TourPackage::factory()->published()->create(['title' => 'Short Galle Escape', 'destination' => 'Galle', 'duration_days' => 3, 'availability_status' => PackageAvailability::Available]);
        TourPackage::factory()->published()->create(['title' => 'Long Kandy Journey', 'destination' => 'Kandy', 'duration_days' => 9, 'availability_status' => PackageAvailability::OnRequest]);
        TourPackage::factory()->create(['title' => 'Draft Galle Package', 'destination' => 'Galle']);

        $this->get(route('packages.index', ['destination' => 'Galle', 'duration' => '1-3', 'availability' => 'available']))
            ->assertOk()
            ->assertSeeText('Short Galle Escape')
            ->assertDontSeeText('Long Kandy Journey')
            ->assertDontSeeText('Draft Galle Package')
            ->assertSee('name="destination"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        TourPackage::factory()->count(13)->published()->create(['destination' => 'Colombo']);
        $this->get(route('packages.index', ['destination' => 'Colombo']))
            ->assertOk()
            ->assertSee('destination=Colombo&amp;page=2', false);
    }

    public function test_package_detail_contains_itinerary_gallery_inquiry_whatsapp_related_and_schema(): void
    {
        $package = TourPackage::factory()->published()->create([
            'title' => 'Southern Coast Discovery', 'destination' => 'Galle', 'availability_status' => PackageAvailability::OnRequest,
            'inclusions' => "Transport\nAccommodation", 'exclusions' => 'Flights',
        ]);
        PackageItinerary::factory()->for($package, 'tourPackage')->create(['title' => 'Arrive in Galle', 'overnight_location' => 'Galle Fort']);
        PackageImage::factory()->for($package, 'tourPackage')->create([
            'alt_text' => 'Galle coast at sunset',
            'variants' => ['small' => ['path' => 'packages/small.webp'], 'medium' => ['path' => 'packages/medium.webp']],
        ]);
        TourPackage::factory()->published()->create(['title' => 'Related Galle Tour', 'destination' => 'Galle']);
        $this->setting('contact.whatsapp_number', '+94 77 123 4567');

        $response = $this->get(route('packages.show', $package));

        $response->assertOk()
            ->assertSeeText('5 days · 4 nights')
            ->assertDontSee('@if(', false)
            ->assertDontSee('@endif', false)
            ->assertSeeText('Arrive in Galle')
            ->assertSeeText('Galle Fort')
            ->assertSeeText('What’s included')
            ->assertSeeText('Related Galle Tour')
            ->assertSee(route('inquiries.package.create', $package), false)
            ->assertSee('https://wa.me/94771234567?text=', false)
            ->assertSee('srcset=', false)
            ->assertSee('alt="Galle coast at sunset"', false)
            ->assertSee('"@type":"TouristTrip"', false)
            ->assertSee('https://schema.org/PreOrder', false);
    }

    public function test_vehicle_catalog_filters_category_capacity_and_published_records(): void
    {
        $cars = VehicleCategory::factory()->published()->create(['name' => 'Cars', 'slug' => 'cars']);
        $bikes = VehicleCategory::factory()->published()->create(['name' => 'Bikes', 'slug' => 'bikes']);
        Vehicle::factory()->for($cars, 'category')->published()->create(['title' => 'Family Car', 'seats' => 6, 'transmission' => 'automatic', 'availability_status' => VehicleAvailability::Available]);
        Vehicle::factory()->for($cars, 'category')->published()->create(['title' => 'Compact Car', 'seats' => 4]);
        Vehicle::factory()->for($bikes, 'category')->published()->create(['title' => 'City Bike', 'seats' => 2]);
        Vehicle::factory()->for($cars, 'category')->create(['title' => 'Draft Car', 'seats' => 8]);

        $this->get(route('vehicles.index', ['category' => 'cars', 'passengers' => 5, 'transmission' => 'automatic']))
            ->assertOk()
            ->assertSeeText('Family Car')
            ->assertDontSeeText('Compact Car')
            ->assertDontSeeText('City Bike')
            ->assertDontSeeText('Draft Car')
            ->assertSee('name="category"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_vehicle_detail_contains_features_gallery_inquiry_whatsapp_related_and_schema(): void
    {
        $category = VehicleCategory::factory()->published()->create(['name' => 'Cars', 'slug' => 'cars']);
        $vehicle = Vehicle::factory()->for($category, 'category')->published()->create([
            'title' => 'Island Family Car', 'availability_status' => VehicleAvailability::Unavailable,
            'seats' => 6, 'luggage_capacity' => 3, 'transmission' => 'automatic', 'fuel_type' => 'petrol', 'rental_terms' => 'Valid licence required.',
            'additional_features' => "Bluetooth audio\nChild seat available",
        ]);
        VehicleImage::factory()->for($vehicle)->create([
            'alt_text' => 'Blue family rental car',
            'variants' => ['small' => ['path' => 'vehicles/small.webp'], 'medium' => ['path' => 'vehicles/medium.webp']],
        ]);
        Vehicle::factory()->for($category, 'category')->published()->create(['title' => 'Related Touring Car']);
        $this->setting('contact.whatsapp_number', '94770001122');

        $response = $this->get(route('vehicles.show', $vehicle));

        $response->assertOk()
            ->assertSeeText('6')
            ->assertSeeText('3 bags')
            ->assertSeeText('Automatic')
            ->assertSeeText('Additional features')
            ->assertSeeText('Bluetooth audio')
            ->assertSeeText('Child seat available')
            ->assertSeeText('Valid licence required.')
            ->assertSeeText('Related Touring Car')
            ->assertSee(route('inquiries.rental.create', $vehicle), false)
            ->assertSee('https://wa.me/94770001122?text=', false)
            ->assertSee('alt="Blue family rental car"', false)
            ->assertSee('"serviceType":"Vehicle rental"', false)
            ->assertSee('https://schema.org/SoldOut', false);
    }

    public function test_draft_scheduled_and_invalid_catalog_slugs_return_not_found(): void
    {
        $draftPackage = TourPackage::factory()->create();
        $scheduledPackage = TourPackage::factory()->scheduled()->create();
        $draftVehicle = Vehicle::factory()->create();
        $scheduledVehicle = Vehicle::factory()->scheduled()->create();

        $this->get(route('packages.show', $draftPackage))->assertNotFound();
        $this->get(route('packages.show', $scheduledPackage))->assertNotFound();
        $this->get('/tour-packages/not-a-real-package')->assertNotFound();
        $this->get(route('vehicles.show', $draftVehicle))->assertNotFound();
        $this->get(route('vehicles.show', $scheduledVehicle))->assertNotFound();
        $this->get('/vehicle-rental/not-a-real-vehicle')->assertNotFound();
    }

    public function test_invalid_catalog_filters_are_rejected(): void
    {
        $this->get(route('packages.index', ['availability' => 'secret', 'duration' => 'forever']))->assertSessionHasErrors(['availability', 'duration']);
        $this->get(route('vehicles.index', ['passengers' => 0, 'sort' => 'unknown']))->assertSessionHasErrors(['passengers', 'sort']);
    }

    private function setting(string $key, mixed $value): void
    {
        (new WebsiteSetting)->forceFill(['group' => 'contact', 'key' => $key, 'value' => $value])->save();
    }
}
