<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBlogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_editor_can_create_post_with_sanitized_content_image_author_and_seo(): void
    {
        $editor = User::factory()->editor()->create();
        $category = BlogCategory::factory()->published()->create();
        $this->actingAs($editor)->post(route('admin.blog.store'), [...$this->data($category), 'featured_image' => UploadedFile::fake()->image('train.jpg', 1600, 900), 'featured_image_alt' => 'Train travelling through Sri Lankan tea country'])->assertRedirect();
        $post = BlogPost::query()->sole();
        $this->assertSame($editor->id, $post->author_id);
        $this->assertSame(PublicationStatus::Published, $post->status);
        $this->assertTrue($post->is_featured);
        $this->assertStringNotContainsString('<script', $post->body);
        $this->assertStringNotContainsString('javascript:', $post->body);
        Storage::disk('public')->assertExists($post->featured_image_path);
        $this->assertSame($post->featured_image_path, $post->seoMetadata->og_image_path);
    }

    public function test_admin_listing_searches_and_filters_posts(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Travel Tips']);
        BlogPost::factory()->for($category, 'category')->published()->create(['title' => 'Ella Train Guide']);
        BlogPost::factory()->create(['title' => 'Hidden Draft']);
        $this->actingAs(User::factory()->editor()->create())->get(route('admin.blog.index', ['search' => 'Ella', 'category' => $category->id, 'status' => 'published']))->assertOk()->assertSeeText('Ella Train Guide')->assertDontSeeText('Hidden Draft');
    }

    public function test_editor_cannot_delete_post_and_category_in_use_is_protected(): void
    {
        $post = BlogPost::factory()->create();
        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.blog.destroy', $post))->assertForbidden();
        $this->actingAs(User::factory()->administrator()->create())->delete(route('admin.blog-categories.destroy', $post->category))->assertSessionHasErrors('category');
    }

    private function data(BlogCategory $category): array
    {
        return ['blog_category_id' => $category->id, 'title' => 'Complete Ella Train Guide', 'slug' => '', 'excerpt' => 'Plan the scenic train journey.', 'body' => '<h2>Route</h2><script>alert(1)</script><p><a href="javascript:bad()">Travel tips</a></p>', 'status' => 'published', 'published_at' => now()->subMinute()->format('Y-m-d H:i:s'), 'is_featured' => '1', 'seo' => ['meta_title' => 'Ella Train Guide', 'meta_description' => 'Plan the Ella train journey.', 'canonical_url' => 'https://ceylonvacationvenues.com/blog/complete-ella-train-guide', 'robots_index' => '1', 'robots_follow' => '1', 'og_title' => 'Ella Train Guide', 'og_description' => 'Scenic Sri Lankan rail travel.', 'og_image_alt' => 'Train in tea country'], 'seo_use_featured_image' => '1'];
    }
}
