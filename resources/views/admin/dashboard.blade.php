<x-admin.layout title="Dashboard">
    <section aria-labelledby="dashboard-heading">
        <div class="mb-6 min-w-0 sm:mb-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700 lg:hidden">Administration</p>
            <h2 id="dashboard-heading" class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Welcome back, {{ auth()->user()->name }}</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 sm:text-base">Here is the latest activity across Ceylon Vacation Venues.</p>
        </div>

        <div class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-admin.stat-card label="Total packages" :value="$metrics['packages']" icon="package" />
            <x-admin.stat-card label="Published packages" :value="$metrics['publishedPackages']" icon="package" tone="green" />
            <x-admin.stat-card label="Vehicles" :value="$metrics['vehicles']" icon="vehicle" tone="cyan" />
            @if($canManageInquiries)
                <x-admin.stat-card label="New inquiries" :value="$metrics['newInquiries']" icon="inquiry" tone="amber" />
                <x-admin.stat-card label="Visa requests" :value="$metrics['visaRequests']" icon="visa" tone="violet" />
                <x-admin.stat-card label="Baggage requests" :value="$metrics['baggageRequests']" icon="baggage" tone="rose" />
            @endif
        </div>
    </section>

    @if($canManageInquiries)
    <section aria-labelledby="recent-inquiries-heading" class="mt-8 min-w-0 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80">
        <div class="flex min-w-0 items-center justify-between gap-4 border-b border-slate-200 px-4 py-5 sm:px-6">
            <div class="min-w-0">
                <h2 id="recent-inquiries-heading" class="text-lg font-semibold tracking-tight text-slate-950">Recent inquiries</h2>
                <p class="mt-1 text-sm text-slate-500">The eight most recent customer requests.</p>
            </div>
            <a href="{{ route('admin.inquiries.index') }}" class="inline-flex min-h-11 shrink-0 items-center rounded-xl px-3 py-2 text-sm font-semibold text-sky-700 outline-none transition hover:bg-sky-50 focus:ring-2 focus:ring-sky-600">
                View all
            </a>
        </div>

        <x-admin.inquiry-list :inquiries="$recentInquiries" />
    </section>
    @endif
</x-admin.layout>
