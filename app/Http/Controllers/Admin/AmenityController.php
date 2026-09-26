<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Amenities\StoreAmenityRequest;
use App\Http\Requests\Admin\Amenities\UpdateAmenityRequest;
use App\Models\Amenity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AmenityController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Amenity::class);

        return view('admin.amenities.index', ['items' => Amenity::withCount('properties')->ordered()->get()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Amenity::class);

        return view('admin.amenities.form', ['item' => new Amenity]);
    }

    public function store(StoreAmenityRequest $r): RedirectResponse
    {
        $d = $r->validated();
        if (blank($d['slug'] ?? null)) {
            unset($d['slug']);
        }Amenity::create($d);

        return redirect()->route('admin.amenities.index')->with('success', 'Amenity created.');
    }

    public function edit(Amenity $amenity): View
    {
        Gate::authorize('update', $amenity);

        return view('admin.amenities.form', ['item' => $amenity]);
    }

    public function update(UpdateAmenityRequest $r, Amenity $amenity): RedirectResponse
    {
        $d = $r->validated();
        if (blank($d['slug'] ?? null)) {
            unset($d['slug']);
        }$amenity->update($d);

        return redirect()->route('admin.amenities.index')->with('success', 'Amenity updated.');
    }

    public function destroy(Amenity $amenity): RedirectResponse
    {
        Gate::authorize('delete', $amenity);
        if ($amenity->properties()->exists()) {
            return back()->withErrors(['amenity' => 'Amenities in use cannot be deleted.']);
        }$amenity->delete();

        return back()->with('success', 'Amenity deleted.');
    }
}
