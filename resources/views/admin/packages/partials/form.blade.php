@php
    $inputClass = 'min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-sky-600 focus:ring-2 focus:ring-sky-100';
    $itineraryData = old('itineraries', $package->exists
        ? $package->itineraries->map(fn ($item) => ['day_number' => $item->day_number, 'title' => $item->title, 'description' => $item->description])->values()->all()
        : [['day_number' => 1, 'title' => '', 'description' => '']]);
    $imageData = $package->exists
        ? $package->images->map(fn ($image) => ['id' => $image->id, 'url' => $image->url('small'), 'alt_text' => $image->alt_text, 'sort_order' => $image->sort_order])->values()->all()
        : [];
    $seo = $package->seoMetadata;
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-200" role="alert">Please correct the highlighted fields and try again.</div>
@endif

<div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <div class="min-w-0 space-y-6">
        <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <h3 class="text-lg font-semibold text-slate-950">Package details</h3>
            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form-field name="title" label="Package name" required class="sm:col-span-2"><input id="title" name="title" value="{{ old('title', $package->title) }}" required maxlength="255" class="{{ $inputClass }}"></x-admin.form-field>
                <x-admin.form-field name="slug" label="SEO slug" hint="Leave blank to generate it from the package name."><input id="slug" name="slug" value="{{ old('slug', $package->slug) }}" maxlength="180" class="{{ $inputClass }}" autocomplete="off"></x-admin.form-field>
                <x-admin.form-field name="destination" label="Destination"><input id="destination" name="destination" value="{{ old('destination', $package->destination) }}" maxlength="255" class="{{ $inputClass }}"></x-admin.form-field>
                <x-admin.form-field name="summary" label="Short description" class="sm:col-span-2"><textarea id="summary" name="summary" rows="3" maxlength="500" class="{{ $inputClass }}">{{ old('summary', $package->summary) }}</textarea></x-admin.form-field>
                <x-admin.form-field name="description" label="Full description" class="sm:col-span-2"><textarea id="description" name="description" rows="8" maxlength="50000" class="{{ $inputClass }}">{{ old('description', $package->description) }}</textarea></x-admin.form-field>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <h3 class="text-lg font-semibold text-slate-950">Duration and pricing</h3>
            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-admin.form-field name="duration_days" label="Days" required><input id="duration_days" name="duration_days" type="number" min="1" max="365" value="{{ old('duration_days', $package->duration_days ?: 1) }}" required class="{{ $inputClass }}"></x-admin.form-field>
                <x-admin.form-field name="duration_nights" label="Nights"><input id="duration_nights" name="duration_nights" type="number" min="0" max="365" value="{{ old('duration_nights', $package->duration_nights) }}" class="{{ $inputClass }}"></x-admin.form-field>
                <x-admin.form-field name="availability_status" label="Availability" required><select id="availability_status" name="availability_status" required class="{{ $inputClass }}">@foreach ($availabilityOptions as $option)<option value="{{ $option->value }}" @selected(old('availability_status', $package->availability_status?->value ?? 'available') === $option->value)>{{ $option->label() }}</option>@endforeach</select></x-admin.form-field>
                <x-admin.form-field name="starting_price" label="Starting price"><input id="starting_price" name="starting_price" type="number" min="0" step="0.01" value="{{ old('starting_price', $package->starting_price) }}" class="{{ $inputClass }}"></x-admin.form-field>
                <x-admin.form-field name="currency" label="Currency" required><input id="currency" name="currency" value="{{ old('currency', $package->currency ?: 'USD') }}" maxlength="3" required class="{{ $inputClass }} uppercase"></x-admin.form-field>
                <x-admin.form-field name="price_basis" label="Price basis" required><select id="price_basis" name="price_basis" class="{{ $inputClass }}">@foreach (['per_person' => 'Per person', 'per_group' => 'Per group', 'per_package' => 'Per package'] as $value => $label)<option value="{{ $value }}" @selected(old('price_basis', $package->price_basis ?: 'per_person') === $value)>{{ $label }}</option>@endforeach</select></x-admin.form-field>
            </div>
        </section>

        <section x-data="packageItinerary({{ Js::from($itineraryData) }})" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <div class="flex items-center justify-between gap-3"><div><h3 class="text-lg font-semibold text-slate-950">Itinerary</h3><p class="mt-1 text-sm text-slate-500">Add and reorder the daily plan.</p></div><button type="button" x-on:click="add" class="min-h-11 shrink-0 rounded-xl border border-sky-200 px-4 text-sm font-semibold text-sky-700">Add day</button></div>
            @error('itineraries')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
            <div class="mt-5 space-y-4">
                <template x-for="(item, index) in items" :key="item.key">
                    <article class="rounded-xl border border-slate-200 p-4">
                        <div class="mb-4 flex items-center justify-between gap-3"><p class="font-semibold text-slate-800" x-text="`Itinerary item ${index + 1}`"></p><div class="flex gap-1"><button type="button" x-on:click="move(index, -1)" :disabled="index === 0" class="size-11 rounded-lg border border-slate-300 disabled:opacity-30" aria-label="Move day up">↑</button><button type="button" x-on:click="move(index, 1)" :disabled="index === items.length - 1" class="size-11 rounded-lg border border-slate-300 disabled:opacity-30" aria-label="Move day down">↓</button><button type="button" x-on:click="remove(index)" class="min-h-11 rounded-lg px-3 text-sm font-semibold text-rose-600">Remove</button></div></div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-[120px_1fr]"><div><label class="mb-2 block text-sm font-medium">Day number</label><input type="number" min="1" max="365" x-model="item.day_number" :name="`itineraries[${index}][day_number]`" class="{{ $inputClass }}" required></div><div><label class="mb-2 block text-sm font-medium">Title</label><input x-model="item.title" :name="`itineraries[${index}][title]`" maxlength="255" class="{{ $inputClass }}" required></div><div class="sm:col-span-2"><label class="mb-2 block text-sm font-medium">Description</label><textarea x-model="item.description" :name="`itineraries[${index}][description]`" rows="4" maxlength="10000" class="{{ $inputClass }}"></textarea></div></div>
                    </article>
                </template>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <h3 class="text-lg font-semibold text-slate-950">Images</h3><p class="mt-1 text-sm text-slate-500">JPEG, PNG, or WebP up to 8 MB. Images are converted and resized automatically.</p>
            @if ($package->exists && $imageData)
                <div x-data="packageExistingImages({{ Js::from($imageData) }})" class="mt-5 space-y-3">
                    <template x-for="(image, index) in items" :key="image.id">
                        <div class="grid min-w-0 gap-3 rounded-xl border border-slate-200 p-3 sm:grid-cols-[96px_minmax(0,1fr)_auto] sm:items-center">
                            <img :src="image.url" :alt="image.alt_text" class="h-24 w-full rounded-lg object-cover sm:w-24">
                            <div class="min-w-0"><label class="mb-1 block text-sm font-medium">Alt text</label><input :name="`existing_images[${image.id}][alt_text]`" x-model="image.alt_text" maxlength="255" required class="{{ $inputClass }}"><input type="hidden" :name="`existing_images[${image.id}][sort_order]`" :value="index"></div>
                            <div class="flex flex-wrap items-center gap-2 sm:flex-col"><label class="flex min-h-11 items-center gap-2 text-sm"><input type="radio" name="featured_image_id" :value="image.id" :checked="index === 0"> Featured</label><div class="flex"><button type="button" x-on:click="move(index, -1)" :disabled="index === 0" class="size-11 rounded-lg border border-slate-300 disabled:opacity-30" aria-label="Move image up">↑</button><button type="button" x-on:click="move(index, 1)" :disabled="index === items.length - 1" class="ml-1 size-11 rounded-lg border border-slate-300 disabled:opacity-30" aria-label="Move image down">↓</button></div><label class="flex min-h-11 items-center gap-2 text-sm text-rose-600"><input type="checkbox" name="delete_image_ids[]" :value="image.id"> Delete</label></div>
                        </div>
                    </template>
                </div>
            @endif
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="featured_image" label="New featured image" hint="Uploading this image makes it the cover."><input id="featured_image" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp" class="block min-h-11 w-full text-sm"></x-admin.form-field><x-admin.form-field name="featured_image_alt" label="Featured image alt text"><input id="featured_image_alt" name="featured_image_alt" value="{{ old('featured_image_alt') }}" maxlength="255" class="{{ $inputClass }}"></x-admin.form-field></div>
            <div x-data="packageGalleryUploads()" class="mt-5"><x-admin.form-field name="gallery_images" label="Add gallery images"><input id="gallery_images" name="gallery_images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple x-on:change="select" class="block min-h-11 w-full text-sm"></x-admin.form-field><div class="mt-4 space-y-3"><template x-for="(file, index) in files" :key="`${file.name}-${index}`"><div><label class="mb-1 block truncate text-sm font-medium" x-text="`Alt text for ${file.name}`"></label><input :name="`gallery_alt_text[${index}]`" x-model="file.alt" required maxlength="255" class="{{ $inputClass }}"></div></template></div></div>
        </section>

        <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <h3 class="text-lg font-semibold text-slate-950">SEO and social sharing</h3>
            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="seo.meta_title" label="Meta title"><input id="seo.meta_title" name="seo[meta_title]" value="{{ old('seo.meta_title', $seo?->meta_title) }}" maxlength="255" class="{{ $inputClass }}"></x-admin.form-field><x-admin.form-field name="seo.canonical_url" label="Canonical URL"><input id="seo.canonical_url" name="seo[canonical_url]" type="url" value="{{ old('seo.canonical_url', $seo?->canonical_url) }}" maxlength="2048" class="{{ $inputClass }}" placeholder="https://ceylonvacationvenues.com/..."></x-admin.form-field><x-admin.form-field name="seo.meta_description" label="Meta description" class="sm:col-span-2"><textarea id="seo.meta_description" name="seo[meta_description]" rows="3" maxlength="500" class="{{ $inputClass }}">{{ old('seo.meta_description', $seo?->meta_description) }}</textarea></x-admin.form-field><x-admin.form-field name="seo.og_title" label="Open Graph title"><input id="seo.og_title" name="seo[og_title]" value="{{ old('seo.og_title', $seo?->og_title) }}" maxlength="255" class="{{ $inputClass }}"></x-admin.form-field><x-admin.form-field name="seo.og_image_alt" label="Social image alt text"><input id="seo.og_image_alt" name="seo[og_image_alt]" value="{{ old('seo.og_image_alt', $seo?->og_image_alt) }}" maxlength="255" class="{{ $inputClass }}"></x-admin.form-field><x-admin.form-field name="seo.og_description" label="Open Graph description" class="sm:col-span-2"><textarea id="seo.og_description" name="seo[og_description]" rows="3" maxlength="500" class="{{ $inputClass }}">{{ old('seo.og_description', $seo?->og_description) }}</textarea></x-admin.form-field></div>
            <div class="mt-5 grid gap-3 sm:grid-cols-3"><label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-4"><input type="hidden" name="seo[robots_index]" value="0"><input type="checkbox" name="seo[robots_index]" value="1" @checked(old('seo.robots_index', $seo?->robots_index ?? true))> Allow indexing</label><label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-4"><input type="hidden" name="seo[robots_follow]" value="0"><input type="checkbox" name="seo[robots_follow]" value="1" @checked(old('seo.robots_follow', $seo?->robots_follow ?? true))> Follow links</label><label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-4"><input type="hidden" name="seo_use_featured_image" value="0"><input type="checkbox" name="seo_use_featured_image" value="1" @checked(old('seo_use_featured_image', filled($seo?->og_image_path)))> Use featured social image</label></div>
        </section>
    </div>

    <aside class="min-w-0 xl:sticky xl:top-28 xl:self-start">
        <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6"><h3 class="font-semibold text-slate-950">Display order</h3><x-admin.form-field name="sort_order" label="Order" hint="Lower values appear first." class="mt-4"><input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $package->sort_order ?? 0) }}" required class="{{ $inputClass }}"></x-admin.form-field><div class="mt-6 grid gap-3"><button type="submit" class="min-h-12 w-full rounded-xl bg-[#082f57] px-5 font-semibold text-white shadow-sm hover:bg-[#0b4278] focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2">{{ $submitLabel }}</button><a href="{{ $package->exists ? route('admin.packages.show', $package) : route('admin.packages.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-4 text-sm font-semibold text-slate-600">Cancel</a></div></section>
    </aside>
</div>
