<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Enums\VehicleAvailability;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Vehicles\IndexVehicleRequest;
use App\Http\Requests\Admin\Vehicles\StoreVehicleRequest;
use App\Http\Requests\Admin\Vehicles\UpdateVehicleRequest;
use App\Http\Requests\Admin\Vehicles\UpdateVehicleStatusRequest;
use App\Models\AuditLog;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\RedirectManager;
use App\Services\VehicleImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class VehicleController extends Controller
{
    public function __construct(private readonly VehicleImageService $images, private readonly RedirectManager $redirects) {}

    public function index(IndexVehicleRequest $request): View
    {
        $filters = $request->validated();
        $vehicles = Vehicle::with(['category:id,name', 'featuredImage'])->withCount('images')
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%")))
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('vehicle_category_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['availability'] ?? null, fn ($q, $v) => $q->where('availability_status', $v))
            ->when(array_key_exists('featured', $filters), fn ($q) => $q->where('is_featured', $request->boolean('featured')))
            ->orderBy('sort_order')->latest('id')->paginate(15)->withQueryString();

        return view('admin.vehicles.index', ['vehicles' => $vehicles, 'filters' => $filters, 'categories' => VehicleCategory::ordered()->get(), 'availabilityOptions' => VehicleAvailability::cases()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Vehicle::class);

        return $this->formView(new Vehicle, 'admin.vehicles.create');
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $stored = collect();
        try {
            $vehicle = DB::transaction(function () use ($request, $validated, $stored) {
                $vehicle = Vehicle::create($this->attributes($validated));
                $this->storeUploads($request, $vehicle, $stored);
                $this->syncSeo($vehicle, $validated, $request->boolean('seo_use_featured_image'));
                $this->audit('vehicle.created', $vehicle);

                return $vehicle;
            });
        } catch (Throwable $e) {
            $stored->each(fn ($image) => $this->images->deleteFiles($image));
            throw $e;
        }

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', 'Vehicle created.');
    }

    public function show(Vehicle $vehicle): View
    {
        Gate::authorize('view', $vehicle);
        $vehicle->load(['category', 'images' => fn ($q) => $q->ordered(), 'seoMetadata'])->loadCount('rentalInquiryDetails');

        return view('admin.vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle): View
    {
        Gate::authorize('update', $vehicle);
        $vehicle->load(['images' => fn ($q) => $q->ordered(), 'seoMetadata']);

        return $this->formView($vehicle, 'admin.vehicles.edit');
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validated();
        $stored = collect();
        $deleted = collect();
        try {
            DB::transaction(function () use ($request, $validated, $vehicle, $stored, $deleted) {
                $oldSlug = $vehicle->slug;
                $vehicle->update($this->attributes($validated));
                $this->redirects->record('/vehicle-rental/'.$oldSlug, '/vehicle-rental/'.$vehicle->slug);
                $this->syncExisting($vehicle, $validated, $deleted);
                $this->storeUploads($request, $vehicle, $stored);
                $this->setFeatured($vehicle, $validated['featured_image_id'] ?? null);
                $this->syncSeo($vehicle, $validated, $request->boolean('seo_use_featured_image'));
                $this->audit('vehicle.updated', $vehicle);
            });
        } catch (Throwable $e) {
            $stored->each(fn ($image) => $this->images->deleteFiles($image));
            throw $e;
        }
        $deleted->each(fn ($image) => $this->images->deleteFiles($image));

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', 'Vehicle updated.');
    }

    public function updateStatus(UpdateVehicleStatusRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['status'])) {
            Gate::authorize('publish', $vehicle);

            $hasMissingAltText = $vehicle->images()
                ->where(function ($query): void {
                    $query->whereNull('alt_text')->orWhere('alt_text', '');
                })->exists();

            if ($data['status'] === 'published' && ($vehicle->images()->doesntExist() || $hasMissingAltText)) {
                throw ValidationException::withMessages(['status' => 'Add at least one image and alt text for every image before publishing.']);
            }
        }
        if (array_key_exists('is_featured', $data)) {
            Gate::authorize('feature', $vehicle);
        }
        if (isset($data['status'])) {
            $status = PublicationStatus::from($data['status']);
            $vehicle->forceFill(['status' => $status, 'published_at' => $status === PublicationStatus::Published ? ($vehicle->published_at ?? now()) : null]);
        }
        if (array_key_exists('is_featured', $data)) {
            $vehicle->is_featured = $data['is_featured'];
        }
        $vehicle->save();
        $this->audit('vehicle.status_updated', $vehicle);

        return back()->with('success', 'Vehicle status updated.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        Gate::authorize('delete', $vehicle);
        if ($vehicle->rentalInquiryDetails()->exists()) {
            return back()->withErrors(['vehicle' => 'Vehicles with rental inquiry history cannot be deleted.']);
        } $images = $vehicle->images()->get();
        DB::transaction(function () use ($vehicle) {
            $this->audit('vehicle.deleted', $vehicle);
            $vehicle->delete();
        });
        $images->each(fn ($image) => $this->images->deleteFiles($image));

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle deleted.');
    }

    private function formView(Vehicle $vehicle, string $view): View
    {
        return view($view, ['vehicle' => $vehicle, 'categories' => VehicleCategory::ordered()->get(), 'availabilityOptions' => VehicleAvailability::cases()]);
    }

    private function attributes(array $data): array
    {
        $attributes = Arr::only($data, ['vehicle_category_id', 'title', 'slug', 'summary', 'description', 'rental_rate', 'currency', 'rate_unit', 'transmission', 'seats', 'luggage_capacity', 'has_air_conditioning', 'availability_status', 'sort_order']);
        $attributes['currency'] = strtoupper($attributes['currency']);
        if (blank($attributes['slug'] ?? null)) {
            unset($attributes['slug']);
        }

        return $attributes;
    }

    private function storeUploads(StoreVehicleRequest $request, Vehicle $vehicle, Collection $stored): void
    {
        if ($request->hasFile('featured_image')) {
            $vehicle->images()->increment('sort_order');
            $stored->push($this->images->store($vehicle, $request->file('featured_image'), $request->string('featured_image_alt')->toString(), 0));
        } $max = $vehicle->images()->max('sort_order');
        $order = $max === null ? 0 : $max + 1;
        foreach ($request->file('gallery_images', []) as $i => $upload) {
            $stored->push($this->images->store($vehicle, $upload, (string) data_get($request->input('gallery_alt_text', []), $i), $order++));
        }
    }

    private function syncExisting(Vehicle $vehicle, array $data, Collection $deleted): void
    {
        foreach ($data['existing_images'] ?? [] as $id => $values) {
            $vehicle->images()->findOrFail($id)->update(Arr::only($values, ['alt_text', 'sort_order']));
        } foreach ($data['delete_image_ids'] ?? [] as $id) {
            $image = $vehicle->images()->findOrFail($id);
            $deleted->push($image->replicate());
            $image->delete();
        }
    }

    private function setFeatured(Vehicle $vehicle, mixed $id): void
    {
        if (! $id) {
            return;
        } $ids = $vehicle->images()->orderBy('sort_order')->pluck('id')->reject(fn ($value) => $value === (int) $id)->prepend((int) $id);
        foreach ($ids as $order => $imageId) {
            $vehicle->images()->whereKey($imageId)->update(['sort_order' => $order]);
        }
    }

    private function syncSeo(Vehicle $vehicle, array $data, bool $useFeatured): void
    {
        $seo = $vehicle->seoMetadata()->firstOrNew();
        $seo->fill($data['seo'] ?? []);
        $featured = $vehicle->images()->orderBy('sort_order')->first();
        $seo->forceFill(['og_image_path' => $useFeatured ? $featured?->path : null, 'og_image_alt' => data_get($data, 'seo.og_image_alt') ?: ($useFeatured ? $featured?->alt_text : null)])->save();
    }

    private function audit(string $action, Vehicle $vehicle): void
    {
        (new AuditLog)->forceFill(['actor_id' => auth()->id(), 'action' => $action, 'subject_type' => Vehicle::class, 'subject_id' => $vehicle->id, 'subject_label' => $vehicle->title, 'request_id' => (string) Str::uuid()])->save();
    }
}
