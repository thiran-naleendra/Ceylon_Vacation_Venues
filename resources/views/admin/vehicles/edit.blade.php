<x-admin.layout :title="'Edit '.$vehicle->title" eyebrow="Rental fleet">
<div class="mb-6"><a href="{{ route('admin.vehicles.show', $vehicle) }}" class="text-sm font-semibold text-sky-700">← {{ $vehicle->title }}</a><h1 class="mt-2 text-2xl font-bold text-slate-950 sm:text-3xl">Edit vehicle</h1></div>
<form method="POST" action="{{ route('admin.vehicles.update', $vehicle) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.vehicles.partials.form', ['submitLabel' => 'Save changes'])</form>
</x-admin.layout>
