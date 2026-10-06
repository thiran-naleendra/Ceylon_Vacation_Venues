<x-admin.layout title="SEO" eyebrow="Search visibility">
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-slate-950 sm:text-3xl">SEO management</h1>
        <p class="text-sm text-slate-500">Review and update search and social metadata across public content.</p>
    </div>

    <nav class="mt-6 flex max-w-full gap-2 overflow-x-auto pb-2" aria-label="SEO content types">
        @foreach($types as $typeKey => $typeConfiguration)
            <a href="{{ route('admin.seo.index', ['type' => $typeKey]) }}" @if($type === $typeKey) aria-current="page" @endif
                class="inline-flex min-h-11 shrink-0 items-center rounded-full border px-4 text-sm font-semibold {{ $type === $typeKey ? 'border-[#082f57] bg-[#082f57] text-white' : 'border-slate-300 bg-white text-slate-700' }}">
                {{ $typeConfiguration['label'] }}
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.seo.index') }}" class="mt-4 grid grid-cols-1 gap-3 rounded-2xl bg-white p-4 ring-1 ring-slate-200 sm:grid-cols-[minmax(0,1fr)_180px_auto]">
        <input type="hidden" name="type" value="{{ $type }}">
        <label><span class="sr-only">Search {{ $configuration['label'] }}</span><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search title or slug" class="min-h-11 w-full rounded-xl border border-slate-300 px-3"></label>
        <label><span class="sr-only">Indexing status</span><select name="indexing" class="min-h-11 w-full rounded-xl border border-slate-300 px-3"><option value="">All indexing states</option><option value="index" @selected(($filters['indexing'] ?? '') === 'index')>Index</option><option value="noindex" @selected(($filters['indexing'] ?? '') === 'noindex')>No index</option><option value="missing" @selected(($filters['indexing'] ?? '') === 'missing')>Metadata missing</option></select></label>
        <button class="min-h-11 rounded-xl bg-slate-900 px-5 font-semibold text-white">Filter</button>
    </form>

    <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @forelse($records as $record)
            @php($metadata = $record->seoMetadata)
            <article class="grid min-w-0 gap-4 border-b border-slate-100 p-4 last:border-b-0 sm:p-5 lg:grid-cols-[minmax(0,1fr)_minmax(220px,1fr)_auto] lg:items-center">
                <div class="min-w-0"><h2 class="break-words font-semibold text-slate-950">{{ data_get($record, $configuration['field']) }}</h2><p class="mt-1 break-all text-xs text-slate-500">/{{ $record->slug }}</p></div>
                <div class="min-w-0 text-sm"><p class="break-words text-slate-700">{{ $metadata?->meta_title ?: 'Uses the content title' }}</p><div class="mt-2 flex flex-wrap gap-2"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $metadata?->robots_index === false ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $metadata?->robots_index === false ? 'No index' : 'Index' }}</span>@if(!$metadata)<span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Metadata not set</span>@endif</div></div>
                <a href="{{ route('admin.seo.edit', [$type, $record->getKey()]) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-4 text-sm font-semibold text-sky-700">Edit SEO</a>
            </article>
        @empty
            <div class="px-5 py-16 text-center"><h2 class="font-semibold text-slate-800">No matching content</h2><p class="mt-1 text-sm text-slate-500">Try changing the filters.</p></div>
        @endforelse
        @if($records->hasPages())<div class="border-t border-slate-200 p-4">{{ $records->links() }}</div>@endif
    </section>
</x-admin.layout>
