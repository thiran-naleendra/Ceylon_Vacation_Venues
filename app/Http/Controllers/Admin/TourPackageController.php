<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PackageAvailability;
use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Packages\IndexTourPackageRequest;
use App\Http\Requests\Admin\Packages\StoreTourPackageRequest;
use App\Http\Requests\Admin\Packages\UpdateTourPackageRequest;
use App\Http\Requests\Admin\Packages\UpdateTourPackageStatusRequest;
use App\Models\AuditLog;
use App\Models\PackageImage;
use App\Models\TourPackage;
use App\Services\PackageImageService;
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

class TourPackageController extends Controller
{
    public function __construct(private readonly PackageImageService $imageService, private readonly RedirectManager $redirects) {}

    public function index(IndexTourPackageRequest $request): View
    {
        $filters = $request->validated();

        $packages = TourPackage::query()
            ->with('featuredImage')
            ->withCount(['itineraries', 'images'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('destination', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['availability'] ?? null, fn ($query, string $availability) => $query->where('availability_status', $availability))
            ->when(array_key_exists('featured', $filters), fn ($query) => $query->where('is_featured', $request->boolean('featured')))
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.packages.index', [
            'packages' => $packages,
            'filters' => $filters,
            'statuses' => PublicationStatus::cases(),
            'availabilityOptions' => PackageAvailability::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', TourPackage::class);

        return view('admin.packages.create', [
            'package' => new TourPackage,
            'availabilityOptions' => PackageAvailability::cases(),
        ]);
    }

    public function store(StoreTourPackageRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $storedImages = collect();

        try {
            $package = DB::transaction(function () use ($request, $validated, $storedImages): TourPackage {
                $package = TourPackage::create($this->packageAttributes($validated));
                $this->syncItineraries($package, $validated['itineraries'] ?? []);
                $this->storeUploads($request, $package, $storedImages);
                $this->syncSeo($package, $validated, $request->boolean('seo_use_featured_image'));
                $this->audit('package.created', $package);

                return $package;
            });
        } catch (Throwable $exception) {
            $storedImages->each(fn (PackageImage $image) => $this->imageService->deleteFiles($image));

            throw $exception;
        }

        return redirect()->route('admin.packages.show', $package)
            ->with('success', 'Package created successfully.');
    }

    public function show(TourPackage $package): View
    {
        Gate::authorize('view', $package);

        $package->load([
            'itineraries' => fn ($query) => $query->ordered(),
            'images' => fn ($query) => $query->ordered(),
            'seoMetadata',
        ])->loadCount('inquiryDetails');

        return view('admin.packages.show', compact('package'));
    }

    public function edit(TourPackage $package): View
    {
        Gate::authorize('update', $package);

        $package->load([
            'itineraries' => fn ($query) => $query->ordered(),
            'images' => fn ($query) => $query->ordered(),
            'seoMetadata',
        ]);

        return view('admin.packages.edit', [
            'package' => $package,
            'availabilityOptions' => PackageAvailability::cases(),
        ]);
    }

    public function update(UpdateTourPackageRequest $request, TourPackage $package): RedirectResponse
    {
        $validated = $request->validated();
        $storedImages = collect();
        $deletedImages = collect();

        try {
            DB::transaction(function () use ($request, $validated, $package, $storedImages, $deletedImages): void {
                $original = Arr::only($package->getOriginal(), ['title', 'slug', 'destination', 'availability_status', 'starting_price', 'currency']);
                $package->update($this->packageAttributes($validated));
                $this->redirects->record('/tour-packages/'.$original['slug'], '/tour-packages/'.$package->slug);
                $this->syncItineraries($package, $validated['itineraries'] ?? []);
                $this->syncExistingImages($package, $validated, $deletedImages);
                $this->storeUploads($request, $package, $storedImages);
                $this->setFeaturedImage($package, $validated['featured_image_id'] ?? null);
                $this->syncSeo($package, $validated, $request->boolean('seo_use_featured_image'));
                $this->audit('package.updated', $package, [
                    'before' => $original,
                    'after' => Arr::only($package->fresh()->toArray(), array_keys($original)),
                ]);
            });
        } catch (Throwable $exception) {
            $storedImages->each(fn (PackageImage $image) => $this->imageService->deleteFiles($image));

            throw $exception;
        }

        $deletedImages->each(fn (PackageImage $image) => $this->imageService->deleteFiles($image));

        return redirect()->route('admin.packages.show', $package)
            ->with('success', 'Package updated successfully.');
    }

    public function updateStatus(UpdateTourPackageStatusRequest $request, TourPackage $package): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['status'])) {
            Gate::authorize('publish', $package);

            if ($validated['status'] === PublicationStatus::Published->value) {
                $package->loadMissing('images');

                if ($package->images->isEmpty() || $package->images->contains(fn (PackageImage $image) => blank($image->alt_text))) {
                    throw ValidationException::withMessages([
                        'status' => 'A package needs at least one optimized image and alt text for every image before publishing.',
                    ]);
                }
            }
        }

        if (array_key_exists('is_featured', $validated)) {
            Gate::authorize('feature', $package);
        }

        DB::transaction(function () use ($validated, $package): void {
            $before = ['status' => $package->status->value, 'is_featured' => $package->is_featured];

            if (isset($validated['status'])) {
                $status = PublicationStatus::from($validated['status']);
                $package->forceFill([
                    'status' => $status,
                    'published_at' => $status === PublicationStatus::Published ? ($package->published_at ?? now()) : null,
                ]);
            }

            if (array_key_exists('is_featured', $validated)) {
                $package->is_featured = $validated['is_featured'];
            }

            $package->save();
            $this->audit('package.status_updated', $package, [
                'before' => $before,
                'after' => ['status' => $package->status->value, 'is_featured' => $package->is_featured],
            ]);
        });

        return back()->with('success', 'Package status updated.');
    }

    public function destroy(TourPackage $package): RedirectResponse
    {
        Gate::authorize('delete', $package);

        if ($package->inquiryDetails()->exists()) {
            return back()->withErrors(['package' => 'This package has inquiry history and cannot be deleted. Unpublish it instead.']);
        }

        $images = $package->images()->get();

        DB::transaction(function () use ($package): void {
            $this->audit('package.deleted', $package);
            $package->delete();
        });

        $images->each(fn (PackageImage $image) => $this->imageService->deleteFiles($image));

        return redirect()->route('admin.packages.index')->with('success', 'Package deleted.');
    }

    /** @param array<string, mixed> $validated */
    private function packageAttributes(array $validated): array
    {
        $attributes = Arr::only($validated, [
            'title', 'slug', 'summary', 'description', 'destination', 'availability_status',
            'duration_days', 'duration_nights', 'starting_price', 'currency', 'price_basis', 'sort_order',
        ]);
        $attributes['currency'] = strtoupper($attributes['currency']);

        if (blank($attributes['slug'] ?? null)) {
            unset($attributes['slug']);
        }

        return $attributes;
    }

    /** @param array<int, array<string, mixed>> $itineraries */
    private function syncItineraries(TourPackage $package, array $itineraries): void
    {
        $package->itineraries()->delete();

        foreach (array_values($itineraries) as $index => $itinerary) {
            $package->itineraries()->create([
                'day_number' => $itinerary['day_number'],
                'title' => $itinerary['title'],
                'description' => $itinerary['description'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    private function storeUploads(StoreTourPackageRequest $request, TourPackage $package, Collection $storedImages): void
    {
        if ($request->hasFile('featured_image')) {
            $package->images()->increment('sort_order');
            $storedImages->push($this->imageService->store(
                $package,
                $request->file('featured_image'),
                $request->string('featured_image_alt')->toString(),
                0,
            ));
        }

        $currentMaximum = $package->images()->max('sort_order');
        $nextOrder = $currentMaximum === null ? 0 : ((int) $currentMaximum) + 1;
        foreach ($request->file('gallery_images', []) as $index => $upload) {
            $storedImages->push($this->imageService->store(
                $package,
                $upload,
                (string) data_get($request->input('gallery_alt_text', []), $index),
                $nextOrder++,
            ));
        }
    }

    /** @param array<string, mixed> $validated */
    private function syncExistingImages(TourPackage $package, array $validated, Collection $deletedImages): void
    {
        foreach ($validated['existing_images'] ?? [] as $imageId => $attributes) {
            $image = $package->images()->findOrFail($imageId);
            $image->update(Arr::only($attributes, ['alt_text', 'sort_order']));
        }

        foreach ($validated['delete_image_ids'] ?? [] as $imageId) {
            $image = $package->images()->findOrFail($imageId);
            $deletedImages->push($image->replicate());
            $image->delete();
        }
    }

    private function setFeaturedImage(TourPackage $package, mixed $featuredImageId): void
    {
        if (! $featuredImageId) {
            return;
        }

        $featured = $package->images()->findOrFail($featuredImageId);
        $orderedIds = $package->images()->orderBy('sort_order')->orderBy('id')->pluck('id')
            ->reject(fn (int $id): bool => $id === $featured->getKey())
            ->prepend($featured->getKey());

        foreach ($orderedIds as $sortOrder => $imageId) {
            $package->images()->whereKey($imageId)->update(['sort_order' => $sortOrder]);
        }
    }

    /** @param array<string, mixed> $validated */
    private function syncSeo(TourPackage $package, array $validated, bool $useFeaturedImage): void
    {
        $seo = $package->seoMetadata()->firstOrNew();
        $seo->fill($validated['seo'] ?? []);

        if ($useFeaturedImage) {
            $featuredImage = $package->images()->orderBy('sort_order')->orderBy('id')->first();
            $seo->forceFill([
                'og_image_path' => $featuredImage?->path,
                'og_image_alt' => data_get($validated, 'seo.og_image_alt') ?: $featuredImage?->alt_text,
            ]);
        } else {
            $seo->forceFill(['og_image_path' => null]);
        }

        $seo->save();
    }

    /** @param array<string, mixed> $changes */
    private function audit(string $action, TourPackage $package, array $changes = []): void
    {
        $audit = new AuditLog;
        $audit->forceFill([
            'actor_id' => auth()->id(),
            'action' => $action,
            'subject_type' => TourPackage::class,
            'subject_id' => $package->getKey(),
            'subject_label' => $package->title,
            'changes' => $changes ?: null,
            'request_id' => (string) Str::uuid(),
        ])->save();
    }
}
