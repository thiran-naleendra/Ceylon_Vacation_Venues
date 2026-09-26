@php
    $title = match ($type) { App\Enums\InquiryType::Package => 'Enquire about ' . $subject->title, App\Enums\InquiryType::Rental => 'Rent ' . $subject->title, App\Enums\InquiryType::Property => 'Enquire about ' . $subject->name, App\Enums\InquiryType::Visa => 'Visa extension inquiry', App\Enums\InquiryType::Baggage => 'Baggage transport inquiry', default => 'Contact us'};
    $action = match ($type) { App\Enums\InquiryType::Package => route('inquiries.package.store', $subject), App\Enums\InquiryType::Rental => route('inquiries.rental.store', $subject), App\Enums\InquiryType::Property => route('inquiries.property.store', $subject), App\Enums\InquiryType::Visa => route('inquiries.visa.store'), App\Enums\InquiryType::Baggage => route('inquiries.baggage.store'), default => route('inquiries.contact.store')};
    $input = 'min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base outline-none focus:border-sky-600 focus:ring-2 focus:ring-sky-100';
@endphp
<x-public.layout :title="$title" robots="noindex,nofollow">
    <section class="bg-gradient-to-br from-[#062b50] to-[#0b5d89] px-4 py-10 text-white sm:py-14">
        <div class="mx-auto max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[.22em] text-amber-300">Ceylon Vacation Venues</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ $title }}</h1>
            <p class="mt-3 max-w-2xl text-sky-100">Tell us what you need. Our Sri Lanka travel team will respond using
                the contact details you provide.</p>
        </div>
    </section>
    <div class="mx-auto grid max-w-5xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1fr)_280px] lg:py-12">
        <div class="min-w-0">
            @if(session('inquiry_success'))
                <div class="rounded-2xl bg-emerald-50 p-6 text-emerald-900 ring-1 ring-emerald-200" role="status">
                    <h2 class="text-xl font-semibold">Thank you. We received your inquiry.</h2>
                    <p class="mt-2 text-sm">Reference: <strong>{{ session('inquiry_success') }}</strong>. Keep this number
                        for future correspondence.</p>
            </div>@else
                @if($errors->any())
                    <div class="mb-5 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-rose-200" role="alert">Please
                review the highlighted fields.</div>@endif
                <form method="POST" action="{{ $action }}"
                    class="space-y-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-7">@csrf<input
                        type="hidden" name="form_token" value="{{ $token }}">
                    <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true"><label
                            for="website">Website</label><input id="website" name="website" tabindex="-1"
                            autocomplete="off"></div>
                    <section>
                        <h2 class="text-lg font-semibold">Your details</h2>
                        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="name"
                                label="Full name" required><input id="name" name="name" value="{{ old('name') }}" required
                                    autocomplete="name" class="{{ $input }}"></x-admin.form-field><x-admin.form-field
                                name="email" label="Email" required><input id="email" name="email" type="email"
                                    value="{{ old('email') }}" required autocomplete="email"
                                    class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="phone"
                                label="Phone / WhatsApp"><input id="phone" name="phone" type="tel"
                                    value="{{ old('phone') }}" autocomplete="tel"
                                    class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="country_code"
                                label="Country code" hint="Two letters, for example GB or AU."><input id="country_code"
                                    name="country_code" value="{{ old('country_code') }}" maxlength="2"
                                    class="{{ $input }} uppercase"></x-admin.form-field></div>
                    </section>
                    @if($type === App\Enums\InquiryType::Package)
                        <section>
                            <h2 class="text-lg font-semibold">Travel plans</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field
                                    name="preferred_start_date" label="Preferred start"><input name="preferred_start_date"
                                        type="date" value="{{ old('preferred_start_date') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="preferred_end_date"
                                    label="Preferred end"><input name="preferred_end_date" type="date"
                                        value="{{ old('preferred_end_date') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="adults"
                                    label="Adults" required><input name="adults" type="number" min="1" max="100"
                                        value="{{ old('adults', 1) }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="children"
                                    label="Children" required><input name="children" type="number" min="0" max="100"
                                        value="{{ old('children', 0) }}" class="{{ $input }}"></x-admin.form-field></div>
                        </section>
                    @elseif($type === App\Enums\InquiryType::Rental)
                        <section>
                            <h2 class="text-lg font-semibold">Rental details</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="pickup_at"
                                    label="Pickup date and time" required><input name="pickup_at" type="datetime-local"
                                        value="{{ old('pickup_at') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="return_at"
                                    label="Return date and time" required><input name="return_at" type="datetime-local"
                                        value="{{ old('return_at') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="pickup_location"
                                    label="Pickup location" required><input name="pickup_location"
                                        value="{{ old('pickup_location') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="return_location"
                                    label="Return location" required><input name="return_location"
                                        value="{{ old('return_location') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="driver_required"
                                    label="Driver" required class="sm:col-span-2"><select name="driver_required"
                                        class="{{ $input }}">
                                        <option value="0" @selected(old('driver_required') === '0')>Self drive</option>
                                        <option value="1" @selected(old('driver_required') === '1')>Driver required</option>
                                    </select></x-admin.form-field></div>
                        </section>
                    @elseif($type === App\Enums\InquiryType::Property)
                        <section><h2 class="text-lg font-semibold">Stay details</h2><div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="check_in_date" label="Check-in date" required><input name="check_in_date" type="date" value="{{ old('check_in_date') }}" required class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="check_out_date" label="Check-out date" required><input name="check_out_date" type="date" value="{{ old('check_out_date') }}" required class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="guests" label="Guests" required class="sm:col-span-2"><input name="guests" type="number" min="1" max="500" value="{{ old('guests',1) }}" required class="{{ $input }}"></x-admin.form-field></div></section>
                    @elseif($type === App\Enums\InquiryType::Visa)
                        <section>
                            <h2 class="text-lg font-semibold">Visa details</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="nationality_code"
                                    label="Nationality country code" required><input name="nationality_code"
                                        value="{{ old('nationality_code') }}" maxlength="2"
                                        class="{{ $input }} uppercase"></x-admin.form-field><x-admin.form-field
                                    name="arrival_date" label="Arrival date" required><input name="arrival_date" type="date"
                                        value="{{ old('arrival_date') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field
                                    name="current_visa_expiry_date" label="Current visa expiry" required><input
                                        name="current_visa_expiry_date" type="date"
                                        value="{{ old('current_visa_expiry_date') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field
                                    name="requested_extension_days" label="Requested extension" required><select
                                        name="requested_extension_days" class="{{ $input }}">@foreach([30, 60, 90] as $days)
                                            <option value="{{ $days }}" @selected(old('requested_extension_days') == $days)>
                                        {{ $days }} days</option>@endforeach
                                    </select></x-admin.form-field></div>
                        </section>
                    @elseif($type === App\Enums\InquiryType::Baggage)
                        <section>
                            <h2 class="text-lg font-semibold">Transport details</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="pickup_location"
                                    label="Pickup location" required><input name="pickup_location"
                                        value="{{ old('pickup_location') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="delivery_location"
                                    label="Delivery location" required><input name="delivery_location"
                                        value="{{ old('delivery_location') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="pickup_at"
                                    label="Pickup date and time" required><input name="pickup_at" type="datetime-local"
                                        value="{{ old('pickup_at') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="bag_count"
                                    label="Number of bags" required><input name="bag_count" type="number" min="1" max="100"
                                        value="{{ old('bag_count', 1) }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="estimated_weight_kg"
                                    label="Estimated weight (kg)"><input name="estimated_weight_kg" type="number" min="0"
                                        step="0.01" value="{{ old('estimated_weight_kg') }}"
                                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field
                                    name="special_instructions" label="Special instructions" class="sm:col-span-2"><textarea
                                        name="special_instructions" rows="3"
                                        class="{{ $input }}">{{ old('special_instructions') }}</textarea></x-admin.form-field>
                            </div>
                    </section>@endif
                    <section>
                        <div class="grid gap-5">@if($type === App\Enums\InquiryType::General)<x-admin.form-field
                            name="subject" label="Subject" required><input name="subject" value="{{ old('subject') }}"
                        class="{{ $input }}"></x-admin.form-field>@endif<x-admin.form-field name="message"
                                label="Message" :required="$type === App\Enums\InquiryType::General"><textarea name="message"
                                    rows="5" class="{{ $input }}">{{ old('message') }}</textarea></x-admin.form-field><label
                                class="flex items-start gap-3 rounded-xl bg-slate-50 p-4 text-sm"><input type="checkbox"
                                    name="privacy_accepted" value="1" class="mt-1 size-5 shrink-0"
                                    @checked(old('privacy_accepted')) required><span>I agree that my details may be used to
                                    respond to this inquiry.</span></label><button
                                class="min-h-12 w-full rounded-xl bg-[#082f57] px-5 font-semibold text-white focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2">Send
                                inquiry</button></div>
                    </section>
            </form>@endif
        </div>
        <aside class="min-w-0">
            <div class="rounded-2xl bg-[#eaf6fb] p-5 ring-1 ring-sky-100">
                <h2 class="font-semibold text-[#062b50]">Need help?</h2>
                <p class="mt-2 text-sm text-slate-600">Use this form for a tracked response from our team.</p>
                @if(is_string($whatsApp) && filled($whatsApp))@php($whatsAppDigits = preg_replace('/\D+/', '', $whatsApp))<a
                    href="https://wa.me/{{ $whatsAppDigits }}"
                    class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white">WhatsApp
                    us</a>@endif
            </div>
        </aside>
    </div>
</x-public.layout>
