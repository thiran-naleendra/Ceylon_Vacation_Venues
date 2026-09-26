<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_editor_can_create_filter_and_update_published_gallery_image(): void
    {
        $editor = User::factory()->editor()->create();
        $category = GalleryAlbum::factory()->published()->create(['title' => 'Beaches']);
        $this->actingAs($editor)->post(route('admin.gallery.store'), ['gallery_album_id' => $category->id, 'title' => 'Sunset at Mirissa', 'alt_text' => 'Orange sunset above Mirissa beach', 'caption' => 'Evening on the south coast.', 'sort_order' => 2, 'status' => 'published', 'image' => UploadedFile::fake()->image('beach.jpg', 1200, 800)])->assertRedirect(route('admin.gallery.index'));
        $image = GalleryImage::query()->sole();
        $this->assertSame(PublicationStatus::Published, $image->status);
        $this->assertSame('image/webp', $image->mime_type);
        Storage::disk('public')->assertExists($image->path);
        Storage::disk('public')->assertExists($image->variants['small']['path']);
        $this->actingAs($editor)->get(route('admin.gallery.index', ['search' => 'Mirissa', 'category' => $category->id, 'status' => 'published']))->assertOk()->assertSeeText('Sunset at Mirissa');
    }

    public function test_gallery_upload_validation_and_delete_authorization_are_enforced(): void
    {
        $category = GalleryAlbum::factory()->create();
        $editor = User::factory()->editor()->create();
        $this->actingAs($editor)->post(route('admin.gallery.store'), ['gallery_album_id' => $category->id, 'title' => 'Unsafe', 'alt_text' => '', 'sort_order' => 0, 'status' => 'draft', 'image' => UploadedFile::fake()->create('image.svg', 5, 'image/svg+xml')])->assertSessionHasErrors(['alt_text', 'image']);
        $image = GalleryImage::factory()->create();
        $this->actingAs($editor)->delete(route('admin.gallery.destroy', $image))->assertForbidden();
    }

    public function test_administrator_can_manage_categories_but_cannot_delete_one_in_use(): void
    {
        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin)->post(route('admin.gallery-categories.store'), ['title' => 'Wildlife', 'slug' => '', 'description' => 'Sri Lankan wildlife', 'status' => 'published', 'sort_order' => 1])->assertRedirect();
        $category = GalleryAlbum::where('title', 'Wildlife')->sole();
        GalleryImage::factory()->for($category, 'album')->create();
        $this->actingAs($admin)->delete(route('admin.gallery-categories.destroy', $category))->assertSessionHasErrors('category');
    }
}
