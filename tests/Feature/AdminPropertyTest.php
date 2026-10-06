<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_editor_can_create_property_with_secure_image_and_seo(): void
    {
        $type = PropertyType::query()->where('slug', 'villa')->firstOrFail();
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.properties.store'), [...$this->data($type), 'featured_image' => UploadedFile::fake()->image('villa.jpg', 1200, 800), 'featured_image_alt' => 'Ocean villa exterior']);
        $property = Property::where('name', 'Ocean View Villa')->sole();
        $response->assertRedirect(route('admin.properties.show', $property));
        $this->assertSame('ocean-view-villa', $property->slug);
        $this->assertSame('USD', $property->currency);
        $this->assertTrue($property->is_negotiable);
        $this->assertSame(PublicationStatus::Draft, $property->status);
        $this->assertSame('Ocean View Villa Sri Lanka', $property->seoMetadata->meta_title);
        $image = $property->images()->sole();
        $this->assertSame('image/webp', $image->mime_type);
        Storage::disk('public')->assertExists($image->path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'property.created', 'subject_id' => $property->id]);
    }

    public function test_validation_and_server_side_authorization_protect_property_actions(): void
    {
        $type = PropertyType::first();
        $this->actingAs(User::factory()->inquiryAgent()->create())->get(route('admin.properties.index'))->assertForbidden();
        $this->actingAs(User::factory()->editor()->create())->post(route('admin.properties.store'), [...$this->data($type), 'property_type_id' => 999999, 'currency' => 'US', 'featured_image' => UploadedFile::fake()->create('shell.php', 5, 'application/x-php')])->assertSessionHasErrors(['property_type_id', 'currency', 'featured_image']);
        $property = Property::factory()->create();
        $other = PropertyImage::factory()->create();
        $this->actingAs(User::factory()->editor()->create())->put(route('admin.properties.update', $property), [...$this->data($property->type), 'delete_image_ids' => [$other->id]])->assertSessionHasErrors('delete_image_ids.0');
        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.properties.destroy', $property))->assertForbidden();
    }

    public function test_publishing_requires_image_alt_and_admin_can_manage_reference_data(): void
    {
        $property = Property::factory()->create();
        $editor = User::factory()->editor()->create();
        $this->actingAs($editor)->patch(route('admin.properties.status', $property), ['status' => 'published'])->assertSessionHasErrors('status');
        PropertyImage::factory()->for($property)->create(['alt_text' => 'Pool villa']);
        $this->actingAs($editor)->patch(route('admin.properties.status', $property), ['status' => 'published', 'is_featured' => '1'])->assertRedirect();
        $this->assertSame(PublicationStatus::Published, $property->refresh()->status);
        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin)->post(route('admin.amenities.store'), ['name' => 'Breakfast', 'slug' => '', 'description' => 'Daily breakfast', 'is_active' => '1', 'sort_order' => 20])->assertRedirect(route('admin.amenities.index'));
        $this->assertDatabaseHas('amenities', ['slug' => 'breakfast']);
    }

    public function test_editor_can_activate_and_deactivate_negotiable_status(): void
    {
        $property = Property::factory()->create();
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->patch(route('admin.properties.status', $property), ['is_negotiable' => '1'])->assertRedirect();
        $this->assertTrue($property->refresh()->is_negotiable);
        $this->actingAs($editor)->get(route('admin.properties.index'))->assertOk()->assertSeeText('Negotiable');

        $this->actingAs($editor)->patch(route('admin.properties.status', $property), ['is_negotiable' => '0'])->assertRedirect();
        $this->assertFalse($property->refresh()->is_negotiable);
    }

    private function data(PropertyType $type): array
    {
        return ['property_type_id' => $type->id, 'name' => 'Ocean View Villa', 'slug' => '', 'short_description' => 'Private coastal villa.', 'description' => 'A spacious villa near the beach.', 'location' => 'Mirissa', 'address_description' => 'Five minutes from the beach.', 'price' => '180.50', 'currency' => 'usd', 'pricing_unit' => 'per_night', 'is_negotiable' => '1', 'bedrooms' => 3, 'bathrooms' => '2.5', 'max_guests' => 6, 'beds_details' => 'Two king beds and two singles.', 'availability_information' => 'Contact us for dates.', 'check_in_time' => '14:00', 'check_out_time' => '11:00', 'sort_order' => 1, 'seo' => ['meta_title' => 'Ocean View Villa Sri Lanka', 'meta_description' => 'Stay at an ocean view villa in Mirissa.', 'canonical_url' => 'https://ceylonvacationvenues.com/villas-houses/ocean-view-villa', 'robots_index' => '1', 'robots_follow' => '1', 'og_title' => 'Ocean View Villa', 'og_description' => 'A private coastal stay.', 'og_image_alt' => 'Ocean villa exterior'], 'seo_use_featured_image' => '1'];
    }
}
