<x-admin.layout :title="'Edit '.$package->title" eyebrow="Packages">
    <div class="mb-6"><a href="{{ route('admin.packages.show', $package) }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-sky-700">← Back to package</a><h2 class="break-words text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Edit {{ $package->title }}</h2></div>
    <form method="POST" action="{{ route('admin.packages.update', $package) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.packages.partials.form', ['submitLabel' => 'Save changes'])
    </form>
</x-admin.layout>
