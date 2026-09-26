<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Pages\UpdatePageRequest;
use App\Http\Requests\Admin\Pages\UpdatePageStatusRequest;
use App\Models\AuditLog;
use App\Models\Page;
use App\Services\AllowedHtmlSanitizer;
use App\Services\PageHeroImageService;
use App\Services\RedirectManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PageController extends Controller
{
    public function __construct(private readonly AllowedHtmlSanitizer $sanitizer, private readonly PageHeroImageService $images, private readonly RedirectManager $redirects) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Page::class);
        $pages = Page::query()->with('sections')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(Page $page): View
    {
        Gate::authorize('update', $page);
        $page->load(['sections', 'seoMetadata']);

        return view('admin.pages.edit', ['page' => $page, 'hero' => $page->sections->firstWhere('section_key', 'hero')?->content ?? []]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $data = $request->validated();
        $oldHero = $page->sections()->where('section_key', 'hero')->first()?->content ?? [];
        $newImage = null;

        if ($request->hasFile('hero.image')) {
            $newImage = $this->images->store($page->page_key, $request->file('hero.image'), (string) data_get($data, 'hero.image_alt'));
        }

        try {
            DB::transaction(function () use ($request, $data, $page, $oldHero, $newImage): void {
                $oldSlug = $page->slug;
                $page->update([
                    'title' => $data['title'], 'slug' => $data['slug'], 'summary' => $data['summary'] ?? null,
                    'body' => $this->sanitizer->sanitize($data['body'] ?? null),
                ]);
                if ($page->page_key !== 'home') {
                    $this->redirects->record('/'.$oldSlug, '/'.$page->slug);
                }

                $heroContent = [
                    'title' => data_get($data, 'hero.title'),
                    'text' => data_get($data, 'hero.text'),
                    'image' => $newImage ?? (data_get($data, 'hero.delete_image') ? null : ($oldHero['image'] ?? null)),
                ];
                $hero = $page->sections()->where('section_key', 'hero')->first() ?? $page->sections()->make();
                $hero->forceFill(['section_key' => 'hero', 'section_type' => 'hero', 'content' => $heroContent, 'is_enabled' => true, 'sort_order' => 0])->save();

                $seo = $page->seoMetadata()->firstOrNew();
                $seo->fill($data['seo'] ?? []);
                $heroImage = $heroContent['image'];
                $seo->forceFill([
                    'og_image_path' => $request->boolean('seo_use_hero_image') ? data_get($heroImage, 'path') : null,
                    'og_image_alt' => data_get($data, 'seo.og_image_alt') ?: ($request->boolean('seo_use_hero_image') ? data_get($heroImage, 'alt') : null),
                ])->save();
                $this->audit('page.updated', $page);
            });
        } catch (Throwable $exception) {
            $this->images->delete($newImage);
            throw $exception;
        }

        if ($newImage || data_get($data, 'hero.delete_image')) {
            $this->images->delete($oldHero['image'] ?? null);
        }

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page updated.');
    }

    public function updateStatus(UpdatePageStatusRequest $request, Page $page): RedirectResponse
    {
        $status = PublicationStatus::from($request->validated('status'));
        $page->forceFill(['status' => $status, 'published_at' => $status === PublicationStatus::Published ? ($page->published_at ?? now()) : null])->save();
        $this->audit('page.status_updated', $page, ['status' => $status->value]);

        return back()->with('success', 'Page status updated.');
    }

    /** @param array<string, mixed>|null $changes */
    private function audit(string $action, Page $page, ?array $changes = null): void
    {
        (new AuditLog)->forceFill(['actor_id' => auth()->id(), 'action' => $action, 'subject_type' => Page::class, 'subject_id' => $page->id, 'subject_label' => $page->title, 'changes' => $changes, 'request_id' => (string) Str::uuid()])->save();
    }
}
