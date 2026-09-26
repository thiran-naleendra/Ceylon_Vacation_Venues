<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Gallery\StoreGalleryImageRequest;
use App\Http\Requests\Admin\Gallery\UpdateGalleryImageRequest;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Services\GalleryImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function __construct(private readonly GalleryImageService $images) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', GalleryImage::class);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:120'], 'category' => ['nullable', 'integer', 'exists:gallery_albums,id'], 'status' => ['nullable', 'in:draft,published']]);
        $images = GalleryImage::with('album:id,title')->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhere('alt_text', 'like', "%{$s}%")))->when($filters['category'] ?? null, fn ($q, $v) => $q->where('gallery_album_id', $v))->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->orderBy('sort_order')->latest('id')->paginate(18)->withQueryString();

        return view('admin.gallery.index', ['images' => $images, 'filters' => $filters, 'categories' => GalleryAlbum::ordered()->get()]);
    }

    public function create(): View
    {
        Gate::authorize('create', GalleryImage::class);

        return view('admin.gallery.form', ['image' => new GalleryImage, 'categories' => GalleryAlbum::ordered()->get()]);
    }

    public function store(StoreGalleryImageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $album = GalleryAlbum::findOrFail($data['gallery_album_id']);
        $image = $this->images->store($album, $request->file('image'), Arr::only($data, ['title', 'alt_text', 'caption', 'sort_order']));
        $this->setStatus($image, $data['status']);

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery image added.');
    }

    public function edit(GalleryImage $gallery): View
    {
        Gate::authorize('update', $gallery);

        return view('admin.gallery.form', ['image' => $gallery, 'categories' => GalleryAlbum::ordered()->get()]);
    }

    public function update(UpdateGalleryImageRequest $request, GalleryImage $gallery): RedirectResponse
    {
        $data = $request->validated();
        $old = $request->hasFile('image') ? clone $gallery : null;
        if ($request->hasFile('image')) {
            $album = GalleryAlbum::findOrFail($data['gallery_album_id']);
            $replacement = $this->images->store($album, $request->file('image'), Arr::only($data, ['title', 'alt_text', 'caption', 'sort_order']));
            $gallery->forceFill(['disk' => $replacement->disk, 'path' => $replacement->path, 'mime_type' => $replacement->mime_type, 'file_size' => $replacement->file_size, 'width' => $replacement->width, 'height' => $replacement->height, 'variants' => $replacement->variants, 'processing_status' => $replacement->processing_status]);
            $replacement->delete();
        } $gallery->update(Arr::only($data, ['gallery_album_id', 'title', 'alt_text', 'caption', 'sort_order']));
        $this->setStatus($gallery, $data['status']);
        if ($old) {
            $this->images->delete($old);
        }

        return back()->with('success', 'Gallery image updated.');
    }

    public function destroy(GalleryImage $gallery): RedirectResponse
    {
        Gate::authorize('delete', $gallery);
        $copy = clone $gallery;
        $gallery->delete();
        $this->images->delete($copy);

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery image deleted.');
    }

    private function setStatus(GalleryImage $image, string $value): void
    {
        $status = PublicationStatus::from($value);
        $image->forceFill(['status' => $status, 'published_at' => $status === PublicationStatus::Published ? ($image->published_at ?? now()) : null, 'is_visible' => $status === PublicationStatus::Published])->save();
    }
}
