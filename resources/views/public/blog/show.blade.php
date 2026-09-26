<x-public.layout :seo="$seo">
    <article>
        <header class="bg-[#062d50] px-4 py-14 text-white sm:px-6 sm:py-20">
            <div class="mx-auto max-w-5xl">
                <nav aria-label="Breadcrumb" class="text-sm text-cyan-200"><a href="{{ route('home') }}">Home</a> / <a
                        href="{{ route('blog.index') }}">Blog</a> / <span aria-current="page">{{ $post->title }}</span>
                </nav>
                <p class="mt-8 text-xs font-bold uppercase tracking-[.24em] text-cyan-300">{{ $post->category->name }}
                </p>
                <h1 class="mt-3 font-display text-4xl font-semibold leading-tight tracking-tight sm:text-6xl">
                    {{ $post->title }}</h1>@if($post->excerpt)
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-sky-100/80">{{ $post->excerpt }}</p>@endif<div
                    class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm text-sky-100/65"><span>By
                        {{ $post->author?->name ?: $businessName }}</span><time
                        datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
                </div>
            </div>
        </header>@if($post->featuredImageUrl())
            <div class="mx-auto -mb-8 max-w-6xl px-4 sm:px-6 lg:px-8"><img src="{{ $post->featuredImageUrl('medium') }}"
                    srcset="{{ $post->featuredImageUrl('small') }} 480w, {{ $post->featuredImageUrl('medium') }} 960w, {{ $post->featuredImageUrl() }} 1800w"
                    sizes="(min-width: 1152px) 1152px, 100vw" alt="{{ $post->featured_image_alt ?: $post->title }}"
                    width="{{ $post->featuredImageWidth('medium') }}" height="{{ $post->featuredImageHeight('medium') }}" fetchpriority="high" decoding="async"
                    class="relative -mt-0 aspect-[16/8] w-full rounded-[1.5rem] object-cover shadow-xl sm:-mt-8"></div>
        @endif<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-20">
            <div class="rich-content">{!! $safeBody !!}</div>
        </div>
    </article>@if($related->isNotEmpty())
        <section class="border-t border-slate-200 bg-white px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <div class="mx-auto max-w-7xl"><x-public.section-heading eyebrow="Keep reading"
                    title="Related travel stories" />
                <div class="mt-9 grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach($related as $relatedPost)<x-public.blog-card :post="$relatedPost" heading-level="h3" />@endforeach</div>
            </div>
    </section>@endif
</x-public.layout>
