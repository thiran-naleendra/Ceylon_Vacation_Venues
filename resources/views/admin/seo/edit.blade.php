<x-admin.layout title="Edit SEO" eyebrow="Search visibility">
    @php($input = 'min-h-11 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100')
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('admin.seo.index', ['type' => $type]) }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-sky-700">← {{ $configuration['label'] }}</a>
        <div class="mt-2 min-w-0"><h1 class="break-words text-2xl font-bold text-slate-950 sm:text-3xl">{{ data_get($record, $configuration['field']) }}</h1><p class="mt-1 break-all text-sm text-slate-500">/{{ $record->slug }}</p></div>
        @if(session('success'))<div class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('success') }}</div>@endif

        <form method="POST" action="{{ route('admin.seo.update', [$type, $record->getKey()]) }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
                <h2 class="text-lg font-semibold">Search result metadata</h2>
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-admin.form-field name="meta_title" label="Meta title" hint="Recommended: about 50–60 characters."><input name="meta_title" maxlength="255" value="{{ old('meta_title', $seo?->meta_title) }}" class="{{ $input }}"></x-admin.form-field>
                    <x-admin.form-field name="canonical_url" label="Canonical URL"><input name="canonical_url" type="url" maxlength="2048" placeholder="https://ceylonvacationvenues.com/..." value="{{ old('canonical_url', $seo?->canonical_url) }}" class="{{ $input }}"></x-admin.form-field>
                    <x-admin.form-field name="meta_description" label="Meta description" hint="Recommended: about 150–160 characters." class="sm:col-span-2"><textarea name="meta_description" maxlength="500" rows="4" class="{{ $input }}">{{ old('meta_description', $seo?->meta_description) }}</textarea></x-admin.form-field>
                </div>
                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="flex min-h-12 items-center gap-3 rounded-xl border border-slate-300 px-4"><input type="hidden" name="robots_index" value="0"><input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', $seo?->robots_index ?? true))><span><strong class="block text-sm">Allow indexing</strong><span class="text-xs text-slate-500">Permit search engines to list this URL.</span></span></label>
                    <label class="flex min-h-12 items-center gap-3 rounded-xl border border-slate-300 px-4"><input type="hidden" name="robots_follow" value="0"><input type="checkbox" name="robots_follow" value="1" @checked(old('robots_follow', $seo?->robots_follow ?? true))><span><strong class="block text-sm">Follow links</strong><span class="text-xs text-slate-500">Permit search engines to follow links.</span></span></label>
                </div>
            </section>
            <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
                <h2 class="text-lg font-semibold">Social sharing</h2>
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-admin.form-field name="og_title" label="Open Graph title"><input name="og_title" maxlength="255" value="{{ old('og_title', $seo?->og_title) }}" class="{{ $input }}"></x-admin.form-field>
                    <x-admin.form-field name="og_image_alt" label="Social image alt text"><input name="og_image_alt" maxlength="255" value="{{ old('og_image_alt', $seo?->og_image_alt) }}" class="{{ $input }}"></x-admin.form-field>
                    <x-admin.form-field name="og_description" label="Open Graph description" class="sm:col-span-2"><textarea name="og_description" maxlength="500" rows="4" class="{{ $input }}">{{ old('og_description', $seo?->og_description) }}</textarea></x-admin.form-field>
                </div>
                @if($seo?->og_image_path)<p class="mt-4 break-all rounded-xl bg-slate-50 p-3 text-xs text-slate-600">Current social image: {{ $seo->og_image_path }}</p>@endif
            </section>
            <div class="sticky bottom-3 rounded-2xl bg-white/95 p-3 shadow-lg ring-1 ring-slate-200 backdrop-blur"><button class="min-h-12 w-full rounded-xl bg-[#082f57] px-5 font-bold text-white sm:w-auto">Save SEO metadata</button></div>
        </form>
    </div>
</x-admin.layout>
