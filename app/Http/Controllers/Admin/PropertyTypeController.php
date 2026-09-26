<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PropertyTypes\StorePropertyTypeRequest;
use App\Http\Requests\Admin\PropertyTypes\UpdatePropertyTypeRequest;
use App\Models\PropertyType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PropertyTypeController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', PropertyType::class);

        return view('admin.property-types.index', ['items' => PropertyType::withCount('properties')->ordered()->get()]);
    }

    public function create(): View
    {
        Gate::authorize('create', PropertyType::class);

        return view('admin.property-types.form', ['item' => new PropertyType]);
    }

    public function store(StorePropertyTypeRequest $r): RedirectResponse
    {
        $d = $r->validated();
        if (blank($d['slug'] ?? null)) {
            unset($d['slug']);
        }$item = PropertyType::create($d);
        $item->forceFill(['status' => $d['status'], 'published_at' => $d['status'] === 'published' ? now() : null])->save();

        return redirect()->route('admin.property-types.index')->with('success', 'Property type created.');
    }

    public function edit(PropertyType $propertyType): View
    {
        Gate::authorize('update', $propertyType);

        return view('admin.property-types.form', ['item' => $propertyType]);
    }

    public function update(UpdatePropertyTypeRequest $r, PropertyType $propertyType): RedirectResponse
    {
        $d = $r->validated();
        if (blank($d['slug'] ?? null)) {
            unset($d['slug']);
        }$propertyType->update($d);
        $propertyType->forceFill(['status' => $d['status'], 'published_at' => $d['status'] === 'published' ? ($propertyType->published_at ?? now()) : null])->save();

        return redirect()->route('admin.property-types.index')->with('success', 'Property type updated.');
    }

    public function destroy(PropertyType $propertyType): RedirectResponse
    {
        Gate::authorize('delete', $propertyType);
        if ($propertyType->properties()->exists()) {
            return back()->withErrors(['type' => 'Property types in use cannot be deleted.']);
        }$propertyType->delete();

        return back()->with('success', 'Property type deleted.');
    }
}
