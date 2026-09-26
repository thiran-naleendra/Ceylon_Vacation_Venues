<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Redirects\StoreRedirectRequest;
use App\Http\Requests\Admin\Redirects\UpdateRedirectRequest;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Redirect::class);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:120'], 'active' => ['nullable', 'boolean']]);
        $redirects = Redirect::query()->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('source_path', 'like', "%{$s}%")->orWhere('destination_path', 'like', "%{$s}%")))->when(array_key_exists('active', $filters), fn ($q) => $q->where('is_active', $request->boolean('active')))->latest()->paginate(20)->withQueryString();

        return view('admin.redirects.index', compact('redirects', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('create', Redirect::class);

        return view('admin.redirects.form', ['redirect' => new Redirect]);
    }

    public function store(StoreRedirectRequest $request): RedirectResponse
    {
        $redirect = Redirect::create(Arr::only($request->validated(), ['source_path', 'destination_path']));
        $redirect->forceFill(Arr::only($request->validated(), ['status_code', 'is_active']))->save();

        return redirect()->route('admin.redirects.index')->with('success', 'Redirect created.');
    }

    public function edit(Redirect $redirect): View
    {
        Gate::authorize('update', $redirect);

        return view('admin.redirects.form', compact('redirect'));
    }

    public function update(UpdateRedirectRequest $request, Redirect $redirect): RedirectResponse
    {
        $redirect->fill(Arr::only($request->validated(), ['source_path', 'destination_path']));
        $redirect->forceFill(Arr::only($request->validated(), ['status_code', 'is_active']))->save();

        return back()->with('success', 'Redirect updated.');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        Gate::authorize('delete', $redirect);
        $redirect->delete();

        return back()->with('success', 'Redirect deleted.');
    }
}
