<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Seo\UpdateSeoMetadataRequest;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Property;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SeoController extends Controller
{
    /** @var array<string, array{class: class-string<Model>, label: string, field: string}> */
    private const TYPES = [
        'pages' => ['class' => Page::class, 'label' => 'Pages', 'field' => 'title'],
        'packages' => ['class' => TourPackage::class, 'label' => 'Tour packages', 'field' => 'title'],
        'vehicles' => ['class' => Vehicle::class, 'label' => 'Vehicles', 'field' => 'title'],
        'properties' => ['class' => Property::class, 'label' => 'Villas & Houses', 'field' => 'name'],
        'blog-posts' => ['class' => BlogPost::class, 'label' => 'Blog posts', 'field' => 'title'],
    ];

    public function index(Request $request): View
    {
        Gate::authorize('publish-content');
        $filters = $request->validate([
            'type' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::TYPES))],
            'search' => ['nullable', 'string', 'max:100'],
            'indexing' => ['nullable', 'string', 'in:index,noindex,missing'],
        ]);
        $type = $filters['type'] ?? 'pages';
        $configuration = self::TYPES[$type];
        $field = $configuration['field'];
        $records = $configuration['class']::query()
            ->with('seoMetadata')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query->where($field, 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
            ->when(($filters['indexing'] ?? null) === 'index', fn ($query) => $query->where(fn ($query) => $query->whereDoesntHave('seoMetadata')->orWhereHas('seoMetadata', fn ($seo) => $seo->where('robots_index', true))))
            ->when(($filters['indexing'] ?? null) === 'noindex', fn ($query) => $query->whereHas('seoMetadata', fn ($seo) => $seo->where('robots_index', false)))
            ->when(($filters['indexing'] ?? null) === 'missing', fn ($query) => $query->whereDoesntHave('seoMetadata'))
            ->orderBy($field)
            ->paginate(20)
            ->withQueryString();

        return view('admin.seo.index', compact('records', 'type', 'configuration', 'filters') + ['types' => self::TYPES]);
    }

    public function edit(string $type, int $record): View
    {
        $model = $this->record($type, $record);
        Gate::authorize('update', $model);
        $model->load('seoMetadata');

        return view('admin.seo.edit', ['record' => $model, 'type' => $type, 'configuration' => self::TYPES[$type], 'seo' => $model->seoMetadata]);
    }

    public function update(UpdateSeoMetadataRequest $request, string $type, int $record): RedirectResponse
    {
        $model = $this->record($type, $record);
        Gate::authorize('update', $model);
        $metadata = $model->seoMetadata()->firstOrNew();
        $metadata->fill($request->validated());
        $metadata->save();

        return redirect()->route('admin.seo.edit', [$type, $model->getKey()])->with('success', 'SEO metadata updated.');
    }

    private function record(string $type, int $record): Model
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type]['class']::query()->findOrFail($record);
    }
}
