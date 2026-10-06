<x-admin.layout title="Packages">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Tour catalogue</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Tour packages</h2>
            <p class="mt-2 text-sm text-slate-500">Manage package content, itineraries, images, publishing, and SEO.</p>
        </div>
        @can('create', App\Models\TourPackage::class)
            <a href="{{ route('admin.packages.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#082f57] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0b4278] focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2">Create package</a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200" role="status">{{ session('success') }}</div>
    @endif
    @error('package')
        <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200" role="alert">{{ $message }}</div>
    @enderror

    <form method="GET" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_180px_180px_150px_auto]">
        <label class="min-w-0">
            <span class="sr-only">Search packages</span>
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, slug or destination"
                class="min-h-11 w-full rounded-xl border border-slate-300 px-3.5 text-sm outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100">
        </label>
        <select name="status" aria-label="Filter by status" class="min-h-11 w-full rounded-xl border border-slate-300 px-3 text-sm outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>@endforeach
        </select>
        <select name="availability" aria-label="Filter by availability" class="min-h-11 w-full rounded-xl border border-slate-300 px-3 text-sm outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100">
            <option value="">All availability</option>
            @foreach ($availabilityOptions as $option)<option value="{{ $option->value }}" @selected(($filters['availability'] ?? '') === $option->value)>{{ $option->label() }}</option>@endforeach
        </select>
        <select name="featured" aria-label="Filter by featured status" class="min-h-11 w-full rounded-xl border border-slate-300 px-3 text-sm outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100">
            <option value="">All packages</option>
            <option value="1" @selected(($filters['featured'] ?? '') === '1')>Featured</option>
            <option value="0" @selected(($filters['featured'] ?? '') === '0')>Not featured</option>
        </select>
        <div class="flex gap-2 sm:col-span-2 xl:col-span-1">
            <button class="min-h-11 flex-1 rounded-xl bg-slate-800 px-4 text-sm font-semibold text-white">Filter</button>
            <a href="{{ route('admin.packages.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-4 text-sm font-semibold text-slate-600">Reset</a>
        </div>
    </form>

    <section class="min-w-0 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @if ($packages->isEmpty())
            <div class="px-5 py-16 text-center"><p class="font-semibold text-slate-800">No packages found</p><p class="mt-1 text-sm text-slate-500">Create a package or adjust the filters.</p></div>
        @else
            <div class="divide-y divide-slate-100 lg:hidden">
                @foreach ($packages as $package)
                    <article class="p-4 sm:p-5">
                        <div class="flex gap-4">
                            <div class="size-20 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                @if ($package->featuredImage)<img src="{{ $package->featuredImage->url('small') }}" alt="{{ $package->featuredImage->alt_text }}" class="size-full object-cover" width="80" height="80">@endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.packages.show', $package) }}" class="break-words font-semibold text-slate-900 hover:text-sky-700">{{ $package->title }}</a>
                                <p class="mt-1 truncate text-sm text-slate-500">{{ $package->destination ?: 'No destination set' }}</p>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 capitalize text-slate-600">{{ $package->status->value }}</span>
                                    <span class="rounded-full bg-sky-50 px-2.5 py-1 text-sky-700">{{ $package->availability_status->label() }}</span>
                                    @if ($package->is_featured)<span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">Featured</span>@endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.packages.show', $package) }}" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl border border-slate-300 text-sm font-semibold text-slate-700">View</a>
                            <a href="{{ route('admin.packages.edit', $package) }}" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-[#082f57] text-sm font-semibold text-white">Edit</a>
                            @can('delete', $package)
                                <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="col-span-2" onsubmit="return confirm('Delete this package? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="hidden overflow-x-auto lg:block">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4">Package</th><th class="px-6 py-4">Duration</th><th class="px-6 py-4">Price</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Content</th><th class="px-6 py-4 text-right">Actions</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($packages as $package)
                            <tr>
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="size-12 shrink-0 overflow-hidden rounded-lg bg-slate-100">@if ($package->featuredImage)<img src="{{ $package->featuredImage->url('small') }}" alt="" class="size-full object-cover" width="48" height="48">@endif</div><div class="min-w-0"><p class="max-w-64 truncate font-semibold text-slate-900">{{ $package->title }}</p><p class="max-w-64 truncate text-xs text-slate-500">{{ $package->destination ?: 'No destination' }}</p></div></div></td>
                                <td class="px-6 py-4 text-slate-600">{{ $package->duration_days }} days</td>
                                <td class="px-6 py-4 text-slate-600">{{ $package->starting_price !== null ? $package->currency.' '.number_format((float) $package->starting_price, 2) : 'On request' }}</td>
                                <td class="px-6 py-4"><span class="capitalize text-slate-700">{{ $package->status->value }}</span>@if ($package->is_featured)<span class="ml-2 text-amber-600">★</span>@endif</td>
                                <td class="px-6 py-4 text-slate-600">{{ $package->itineraries_count }} days · {{ $package->images_count }} images</td>
                                <td class="px-6 py-4"><div class="flex items-center justify-end gap-4"><a href="{{ route('admin.packages.show', $package) }}" class="font-semibold text-sky-700">View</a><a href="{{ route('admin.packages.edit', $package) }}" class="font-semibold text-slate-700">Edit</a>@can('delete', $package)<form method="POST" action="{{ route('admin.packages.destroy', $package) }}" onsubmit="return confirm('Delete this package? This action cannot be undone.')">@csrf @method('DELETE')<button type="submit" class="font-semibold text-red-700">Delete</button></form>@endcan</div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @if ($packages->hasPages())<div class="border-t border-slate-200 px-4 py-4 sm:px-6">{{ $packages->links() }}</div>@endif
    </section>
</x-admin.layout>
