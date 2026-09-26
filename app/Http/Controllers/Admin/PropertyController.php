<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Properties\IndexPropertyRequest;
use App\Http\Requests\Admin\Properties\StorePropertyRequest;
use App\Http\Requests\Admin\Properties\UpdatePropertyRequest;
use App\Http\Requests\Admin\Properties\UpdatePropertyStatusRequest;
use App\Models\Amenity;
use App\Models\AuditLog;
use App\Models\Property;
use App\Models\PropertyType;
use App\Services\PropertyImageService;
use App\Services\RedirectManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PropertyController extends Controller
{
    public function __construct(private readonly PropertyImageService $images, private readonly RedirectManager $redirects) {}

    public function index(IndexPropertyRequest $request): View
    {
        $f = $request->validated();
        $properties = Property::with(['type:id,name', 'featuredImage'])->withCount('images')->when($f['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%")->orWhere('location', 'like', "%{$s}%")))->when($f['type'] ?? null, fn ($q, $v) => $q->where('property_type_id', $v))->when($f['location'] ?? null, fn ($q, $v) => $q->where('location', $v))->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when(array_key_exists('featured', $f), fn ($q) => $q->where('is_featured', $request->boolean('featured')))->ordered()->paginate(15)->withQueryString();

        return view('admin.properties.index', ['properties' => $properties, 'filters' => $f, 'types' => PropertyType::ordered()->get(), 'locations' => Property::whereNotNull('location')->distinct()->orderBy('location')->pluck('location')]);
    }

    public function create(): View
    {
        Gate::authorize('create', Property::class);

        return $this->form(new Property, 'admin.properties.create');
    }

    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $stored = collect();
        try {
            $property = DB::transaction(function () use ($request, $data, $stored) {
                $property = Property::create($this->attributes($data));
                $property->amenities()->sync($data['amenity_ids'] ?? []);
                $this->uploads($request, $property, $stored);
                $this->seo($property, $data, $request->boolean('seo_use_featured_image'));
                $this->audit('property.created', $property);

                return $property;
            });
        } catch (Throwable $e) {
            $stored->each(fn ($i) => $this->images->deleteFiles($i));
            throw $e;
        }

        return redirect()->route('admin.properties.show', $property)->with('success', 'Property created.');
    }

    public function show(Property $property): View
    {
        Gate::authorize('view', $property);
        $property->load(['type', 'amenities', 'images' => fn ($q) => $q->ordered(), 'seoMetadata'])->loadCount('inquiryDetails');

        return view('admin.properties.show', compact('property'));
    }

    public function edit(Property $property): View
    {
        Gate::authorize('update', $property);
        $property->load(['amenities', 'images' => fn ($q) => $q->ordered(), 'seoMetadata']);

        return $this->form($property, 'admin.properties.edit');
    }

    public function update(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $data = $request->validated();
        $stored = collect();
        $deleted = collect();
        try {
            DB::transaction(function () use ($request, $data, $property, $stored, $deleted) {
                $old = $property->slug;
                $property->update($this->attributes($data));
                $property->amenities()->sync($data['amenity_ids'] ?? []);
                $this->redirects->record('/villas-houses/'.$old, '/villas-houses/'.$property->slug);
                foreach ($data['existing_images'] ?? [] as $id => $values) {
                    $property->images()->findOrFail($id)->update(Arr::only($values, ['alt_text', 'caption', 'sort_order']));
                }foreach ($data['delete_image_ids'] ?? [] as $id) {
                    $image = $property->images()->findOrFail($id);
                    $deleted->push($image->replicate());
                    $image->delete();
                }$this->uploads($request, $property, $stored);
                if ($id = $data['featured_image_id'] ?? null) {
                    $ids = $property->images()->ordered()->pluck('id')->reject(fn ($v) => $v === (int) $id)->prepend((int) $id);
                    foreach ($ids as $order => $imageId) {
                        $property->images()->whereKey($imageId)->update(['sort_order' => $order]);
                    }
                }$this->seo($property, $data, $request->boolean('seo_use_featured_image'));
                $this->audit('property.updated', $property);
            });
        } catch (Throwable $e) {
            $stored->each(fn ($i) => $this->images->deleteFiles($i));
            throw $e;
        }$deleted->each(fn ($i) => $this->images->deleteFiles($i));

        return redirect()->route('admin.properties.show', $property)->with('success', 'Property updated.');
    }

    public function updateStatus(UpdatePropertyStatusRequest $request, Property $property): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['status'])) {
            Gate::authorize('publish', $property);
            if ($data['status'] === 'published' && ($property->images()->doesntExist() || $property->images()->where(fn ($q) => $q->whereNull('alt_text')->orWhere('alt_text', ''))->exists())) {
                throw ValidationException::withMessages(['status' => 'Add at least one image and alt text for every image before publishing.']);
            }$status = PublicationStatus::from($data['status']);
            $property->forceFill(['status' => $status, 'published_at' => $status === PublicationStatus::Published ? ($property->published_at ?? now()) : null]);
        }if (array_key_exists('is_featured', $data)) {
            Gate::authorize('feature', $property);
            $property->is_featured = $data['is_featured'];
        }$property->save();
        $this->audit('property.status_updated', $property);

        return back()->with('success', 'Property status updated.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        Gate::authorize('delete', $property);
        if ($property->inquiryDetails()->exists()) {
            return back()->withErrors(['property' => 'Properties with inquiry history cannot be deleted.']);
        }$images = $property->images()->get();
        DB::transaction(function () use ($property) {
            $this->audit('property.deleted', $property);
            $property->delete();
        });
        $images->each(fn ($i) => $this->images->deleteFiles($i));

        return redirect()->route('admin.properties.index')->with('success', 'Property deleted.');
    }

    private function form(Property $property, string $view): View
    {
        return view($view, ['property' => $property, 'types' => PropertyType::ordered()->get(), 'amenities' => Amenity::where('is_active', true)->ordered()->get()]);
    }

    private function attributes(array $data): array
    {
        $a = Arr::only($data, ['property_type_id', 'name', 'slug', 'short_description', 'description', 'location', 'address_description', 'price', 'currency', 'pricing_unit', 'bedrooms', 'bathrooms', 'max_guests', 'beds_details', 'availability_information', 'check_in_time', 'check_out_time', 'sort_order']);
        $a['currency'] = strtoupper($a['currency']);
        if (blank($a['slug'] ?? null)) {
            unset($a['slug']);
        }

        return $a;
    }

    private function uploads(StorePropertyRequest $request, Property $property, Collection $stored): void
    {
        if ($request->hasFile('featured_image')) {
            $property->images()->increment('sort_order');
            $stored->push($this->images->store($property, $request->file('featured_image'), $request->string('featured_image_alt')->toString(), 0));
        }$order = ($property->images()->max('sort_order') ?? -1) + 1;
        foreach ($request->file('gallery_images', []) as $i => $upload) {
            $stored->push($this->images->store($property, $upload, (string) data_get($request->input('gallery_alt_text', []), $i), $order++));
        }
    }

    private function seo(Property $property, array $data, bool $use): void
    {
        $seo = $property->seoMetadata()->firstOrNew();
        $seo->fill($data['seo'] ?? []);
        $image = $property->images()->ordered()->first();
        $seo->forceFill(['og_image_path' => $use ? $image?->path : null, 'og_image_alt' => data_get($data, 'seo.og_image_alt') ?: ($use ? $image?->alt_text : null)])->save();
    }

    private function audit(string $action, Property $property): void
    {
        (new AuditLog)->forceFill(['actor_id' => auth()->id(), 'action' => $action, 'subject_type' => Property::class, 'subject_id' => $property->id, 'subject_label' => $property->name, 'request_id' => (string) Str::uuid()])->save();
    }
}
