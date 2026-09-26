<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

class FullApplicationQaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_all_primary_public_get_routes_render_successfully(): void
    {
        foreach ([
            ['home', 'home', 'Home'],
            ['about', 'about', 'About'],
            ['privacy-policy', 'privacy-policy', 'Privacy Policy'],
            ['terms-and-conditions', 'terms-and-conditions', 'Terms and Conditions'],
        ] as [$pageKey, $slug, $title]) {
            Page::factory()->published()->create(['page_key' => $pageKey, 'slug' => $slug, 'title' => $title]);
        }

        foreach ([
            'home', 'about', 'packages.index', 'vehicles.index', 'services.visa', 'services.baggage',
            'gallery.index', 'blog.index', 'inquiries.contact.create', 'privacy', 'terms', 'sitemap', 'robots',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_missing_public_resources_and_unknown_paths_return_safe_404_responses(): void
    {
        foreach (['/tour-packages/not-found', '/vehicle-rental/not-found', '/blog/not-found', '/unknown-page'] as $path) {
            $this->get($path)
                ->assertNotFound()
                ->assertDontSee('vendor/laravel', false)
                ->assertDontSee('SQLSTATE', false);
        }
    }

    public function test_production_500_response_does_not_expose_exception_details(): void
    {
        config(['app.debug' => false]);
        $response = app(ExceptionHandler::class)->render(
            Request::create('/failing-page'),
            new RuntimeException('private implementation detail'),
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringNotContainsString('private implementation detail', (string) $response->getContent());
        $this->assertStringNotContainsString('RuntimeException', (string) $response->getContent());
    }

    public function test_shared_form_fields_have_implicit_accessible_labels(): void
    {
        $html = $this->get(route('inquiries.contact.create'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<label class="block">.*?<input[^>]+name="name"/s', $html);
        $this->assertMatchesRegularExpression('/<label class="block">.*?<textarea[^>]+name="message"/s', $html);
    }
}
