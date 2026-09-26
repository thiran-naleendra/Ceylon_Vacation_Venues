<x-admin.layout title="Add vehicle" eyebrow="Rental fleet">
<div class="mb-6"><a href="{{ route('admin.vehicles.index') }}" class="text-sm font-semibold text-sky-700">← Vehicles</a><h1 class="mt-2 text-2xl font-bold text-slate-950 sm:text-3xl">Add vehicle</h1></div>
<form method="POST" action="{{ route('admin.vehicles.store') }}" enctype="multipart/form-data">@csrf @include('admin.vehicles.partials.form', ['submitLabel' => 'Create vehicle'])</form>
</x-admin.layout>
