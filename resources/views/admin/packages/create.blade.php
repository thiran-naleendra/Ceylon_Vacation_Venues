<x-admin.layout title="Create package" eyebrow="Packages">
    <div class="mb-6"><a href="{{ route('admin.packages.index') }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-sky-700">← Back to packages</a><h2 class="text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Create tour package</h2></div>
    <form method="POST" action="{{ route('admin.packages.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.packages.partials.form', ['submitLabel' => 'Create package'])
    </form>
</x-admin.layout>
