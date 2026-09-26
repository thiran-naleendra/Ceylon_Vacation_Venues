<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Gallery\StoreGalleryCategoryRequest;
use App\Http\Requests\Admin\Gallery\UpdateGalleryCategoryRequest;
use App\Models\GalleryAlbum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GalleryCategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', GalleryAlbum::class);

        return view('admin.gallery-categories.index', ['categories' => GalleryAlbum::withCount('images')->ordered()->get()]);
    }

    public function create(): View
    {
        Gate::authorize('create', GalleryAlbum::class);

        return view('admin.gallery-categories.form', ['category' => new GalleryAlbum]);
    }

    public function store(StoreGalleryCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $status = PublicationStatus::from($data['status']);
        unset($data['status']);
        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        } $category = GalleryAlbum::create($data);
        $category->forceFill(['status' => $status, 'published_at' => $status === PublicationStatus::Published ? now() : null])->save();

        return redirect()->route('admin.gallery-categories.index')->with('success', 'Gallery category created.');
    }

    public function edit(GalleryAlbum $galleryCategory): View
    {
        Gate::authorize('update', $galleryCategory);

        return view('admin.gallery-categories.form', ['category' => $galleryCategory]);
    }

    public function update(UpdateGalleryCategoryRequest $request, GalleryAlbum $galleryCategory): RedirectResponse
    {
        $data = $request->validated();
        $status = PublicationStatus::from($data['status']);
        unset($data['status']);
        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        } $galleryCategory->update($data);
        $galleryCategory->forceFill(['status' => $status, 'published_at' => $status === PublicationStatus::Published ? ($galleryCategory->published_at ?? now()) : null])->save();

        return back()->with('success', 'Gallery category updated.');
    }

    public function destroy(GalleryAlbum $galleryCategory): RedirectResponse
    {
        Gate::authorize('delete', $galleryCategory);
        if ($galleryCategory->images()->exists()) {
            return back()->withErrors(['category' => 'Categories containing images cannot be deleted.']);
        } $galleryCategory->delete();

        return back()->with('success', 'Gallery category deleted.');
    }
}
