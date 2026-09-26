<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Vehicles\StoreVehicleCategoryRequest;
use App\Http\Requests\Admin\Vehicles\UpdateVehicleCategoryRequest;
use App\Models\VehicleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VehicleCategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', VehicleCategory::class);

        return view('admin.vehicle-categories.index', ['categories' => VehicleCategory::withCount('vehicles')->ordered()->get()]);
    }

    public function create(): View
    {
        Gate::authorize('create', VehicleCategory::class);

        return view('admin.vehicle-categories.form', ['category' => new VehicleCategory]);
    }

    public function store(StoreVehicleCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        }

        $category = VehicleCategory::create($data);
        $category->forceFill(['status' => $data['status'], 'published_at' => $data['status'] === 'published' ? now() : null])->save();

        return redirect()->route('admin.vehicle-categories.index')->with('success', "{$category->name} created.");
    }

    public function edit(VehicleCategory $vehicleCategory): View
    {
        Gate::authorize('update', $vehicleCategory);

        return view('admin.vehicle-categories.form', ['category' => $vehicleCategory]);
    }

    public function update(UpdateVehicleCategoryRequest $request, VehicleCategory $vehicleCategory): RedirectResponse
    {
        $data = $request->validated();
        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        }

        $vehicleCategory->update($data);
        $vehicleCategory->forceFill(['status' => $data['status'], 'published_at' => $data['status'] === 'published' ? ($vehicleCategory->published_at ?? now()) : null])->save();

        return redirect()->route('admin.vehicle-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(VehicleCategory $vehicleCategory): RedirectResponse
    {
        Gate::authorize('delete', $vehicleCategory);
        if ($vehicleCategory->vehicles()->exists() || $vehicleCategory->rentalInquiryDetails()->exists()) {
            return back()->withErrors(['category' => 'Categories in use cannot be deleted.']);
        }

        $vehicleCategory->delete();

        return back()->with('success', 'Category deleted.');
    }
}
