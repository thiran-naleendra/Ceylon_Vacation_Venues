<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBlogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_index_only_lists_currently_published_posts(): void
    {
        $publishedCategory = BlogCategory::factory()->published()->create(['name' => 'Travel Guides']);
        $draftCategory = BlogCategory::factory()->create(['name' => 'Private Category']);
        BlogPost::factory()->for($publishedCategory, 'category')->published()->create(['title' => 'Published Travel Story']);
        BlogPost::factory()->for($publishedCategory, 'category')->create(['title' => 'Draft Story']);
        BlogPost::factory()->for($publishedCategory, 'category')->scheduled()->create(['title' => 'Future Story']);
        BlogPost::factory()->for($draftCategory, 'category')->published()->create(['title' => 'Published Post']);

        $this->get(route('blog.index'))->assertOk()->assertSeeText('Published Travel Story')->assertSeeText('Published Post')->assertDontSeeText('Draft Story')->assertDontSeeText('Future Story');
    }

    public function test_listing_supports_featured_posts_category_filter_pagination_and_seo(): void
    {
        $guides = BlogCategory::factory()->published()->create(['name' => 'Guides', 'slug' => 'guides']);
        $news = BlogCategory::factory()->published()->create(['name' => 'News', 'slug' => 'news']);
        BlogPost::factory()->for($guides, 'category')->published()->create(['title' => 'Featured Guide', 'is_featured' => true, 'excerpt' => 'A featured guide.']);
        BlogPost::factory()->for($news, 'category')->published()->create(['title' => 'Other News']);
        BlogPost::factory()->count(12)->for($guides, 'category')->published()->create();
        BlogPost::factory()->for($guides, 'category')->published()->create(['featured_image_path' => 'blog/card.webp', 'featured_image_alt' => 'Sri Lanka travel guide']);

        $this->get(route('blog.index', ['category' => 'guides']))->assertOk()
            ->assertSeeText('Featured Guide')->assertSeeText('Featured story')->assertDontSeeText('Other News')
            ->assertSee('category=guides&amp;page=2', false)->assertSee('rel="canonical"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)->assertSee('loading="lazy"', false);
    }

    public function test_public_post_has_safe_content_article_schema_breadcrumbs_and_related_posts(): void
    {
        $category = BlogCategory::factory()->published()->create(['name' => 'Sri Lanka Guides']);
        $post = BlogPost::factory()->for($category, 'category')->published()->create([
            'title' => 'Guide to Galle', 'excerpt' => 'Explore historic Galle.',
            'body' => '<h2>Galle Fort</h2><p>Safe article text.</p><script>alert(1)</script>',
            'featured_image_path' => 'blog/galle.webp', 'featured_image_alt' => 'Galle Fort at sunset',
            'featured_image_variants' => ['small' => ['path' => 'blog/galle-small.webp', 'width' => 480, 'height' => 270], 'medium' => ['path' => 'blog/galle-medium.webp', 'width' => 960, 'height' => 540]],
        ]);
        BlogPost::factory()->for($category, 'category')->published()->create(['title' => 'Related Coast Guide']);
        $post->seoMetadata()->create(['meta_title' => 'Galle Travel Guide', 'meta_description' => 'Plan a visit to Galle.', 'canonical_url' => 'https://ceylonvacationvenues.com/blog/galle-guide', 'robots_index' => true, 'robots_follow' => true]);
        (new WebsiteSetting)->forceFill(['group' => 'business', 'key' => 'business.name', 'value' => 'CVV Travel'])->save();

        $this->get(route('blog.show', $post))->assertOk()->assertSee('<title>Galle Travel Guide</title>', false)
            ->assertSee('rel="canonical" href="https://ceylonvacationvenues.com/blog/galle-guide"', false)
            ->assertSee('alt="Galle Fort at sunset"', false)->assertSeeText('Galle Fort')->assertSeeText('Safe article text.')
            ->assertDontSee('<script>', false)->assertSee('"@type":"Article"', false)->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSeeText('CVV Travel')->assertSeeText('Related Coast Guide');
    }

    public function test_draft_future_and_invalid_posts_return_not_found(): void
    {
        $this->get(route('blog.show', BlogPost::factory()->create()))->assertNotFound();
        $this->get(route('blog.show', BlogPost::factory()->scheduled()->create()))->assertNotFound();
        $this->get('/blog/not-a-real-post')->assertNotFound();
    }

    public function test_blog_has_database_backed_empty_state_without_coming_soon_copy(): void
    {
        $this->get(route('blog.index'))->assertOk()->assertSeeText('No published articles yet')->assertDontSeeText('Coming Soon')->assertDontSeeText('coming soon');
    }
}
