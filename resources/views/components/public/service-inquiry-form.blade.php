@props(['type', 'token', 'whatsApp' => null])
@php
    $action = match ($type) { App\Enums\InquiryType::Visa => route('inquiries.visa.store'), App\Enums\InquiryType::Baggage => route('inquiries.baggage.store'), default => route('inquiries.contact.store')};
    $input = 'min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base outline-none focus:border-cyan-700 focus:ring-2 focus:ring-cyan-100';
    $whatsAppDigits = is_string($whatsApp) ? preg_replace('/\D+/', '', $whatsApp) : null;
@endphp
<div id="inquiry" class="scroll-mt-6">
    @if(session('inquiry_success'))
        <div class="rounded-2xl bg-emerald-50 p-6 text-emerald-900 ring-1 ring-emerald-200" role="status">
            <h2 class="font-display text-2xl font-semibold">Thank you. We received your inquiry.</h2>
            <p class="mt-2 text-sm">Reference: <strong>{{ session('inquiry_success') }}</strong></p>
    </div>@else
        @if($errors->any())
            <div class="mb-5 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-rose-200" role="alert">Please review
        the highlighted fields.</div>@endif
        <form method="POST" action="{{ $action }}"
            class="space-y-6 rounded-[1.5rem] bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-7">@csrf<input type="hidden"
                name="form_token" value="{{ $token }}">
            <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true"><label
                    for="service-website">Website</label><input id="service-website" name="website" tabindex="-1"
                    autocomplete="off"></div>
            <div>
                <h2 class="font-display text-2xl font-semibold text-[#082d4f]">
                    {{ $type === App\Enums\InquiryType::General ? 'Send us a message' : 'Tell us what you need' }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Complete the details below and our team will respond using
                    the contact information you provide.</p>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="name" label="Full name"
                    required><input name="name" value="{{ old('name') }}" required autocomplete="name"
                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="email" label="Email"
                    required><input name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="phone"
                    label="Phone / WhatsApp"><input name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel"
                        class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="country_code"
                    label="Country code"><input name="country_code" value="{{ old('country_code') }}" maxlength="2"
                        placeholder="GB" class="{{ $input }} uppercase"></x-admin.form-field></div>
            @if($type === App\Enums\InquiryType::Visa)
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="nationality_code"
                        label="Nationality country code" required><input name="nationality_code"
                            value="{{ old('nationality_code') }}" maxlength="2" required
                            class="{{ $input }} uppercase"></x-admin.form-field><x-admin.form-field name="arrival_date"
                        label="Arrival date" required><input name="arrival_date" type="date" value="{{ old('arrival_date') }}"
                            required class="{{ $input }}"></x-admin.form-field><x-admin.form-field
                        name="current_visa_expiry_date" label="Current visa expiry" required><input
                            name="current_visa_expiry_date" type="date" value="{{ old('current_visa_expiry_date') }}" required
                            class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="requested_extension_days"
                        label="Requested extension" required><select name="requested_extension_days"
                            class="{{ $input }}">@foreach([30, 60, 90] as $days)
                                <option value="{{ $days }}" @selected(old('requested_extension_days') == $days)>{{ $days }} days
                            </option>@endforeach
                        </select></x-admin.form-field></div>
            @elseif($type === App\Enums\InquiryType::Baggage)
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2"><x-admin.form-field name="pickup_location"
                        label="Pickup location" required><input name="pickup_location" value="{{ old('pickup_location') }}"
                            required class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="delivery_location"
                        label="Delivery location" required><input name="delivery_location"
                            value="{{ old('delivery_location') }}" required
                            class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="pickup_at"
                        label="Pickup date and time" required><input name="pickup_at" type="datetime-local"
                            value="{{ old('pickup_at') }}" required
                            class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="bag_count"
                        label="Number of bags" required><input name="bag_count" type="number" min="1" max="100"
                            value="{{ old('bag_count', 1) }}" required
                            class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="estimated_weight_kg"
                        label="Estimated weight (kg)"><input name="estimated_weight_kg" type="number" min="0" step="0.01"
                            value="{{ old('estimated_weight_kg') }}"
                            class="{{ $input }}"></x-admin.form-field><x-admin.form-field name="special_instructions"
                        label="Special instructions"><textarea name="special_instructions" rows="3"
                            class="{{ $input }}">{{ old('special_instructions') }}</textarea></x-admin.form-field></div>
            @else<x-admin.form-field name="subject" label="Subject" required><input name="subject"
            value="{{ old('subject') }}" required class="{{ $input }}"></x-admin.form-field>@endif
            <x-admin.form-field name="message" label="Message" :required="$type === App\Enums\InquiryType::General"><textarea
                    name="message" rows="5" @if($type === App\Enums\InquiryType::General) required @endif
                    class="{{ $input }}">{{ old('message') }}</textarea></x-admin.form-field><label
                class="flex items-start gap-3 rounded-xl bg-slate-50 p-4 text-sm leading-6"><input type="checkbox"
                    name="privacy_accepted" value="1" class="mt-1 size-5 shrink-0" @checked(old('privacy_accepted'))
                    required><span>I agree that my details may be used to respond to this inquiry.</span></label><button
                class="min-h-12 w-full rounded-xl bg-[#082d4f] px-5 font-bold text-white">Send inquiry</button>
    </form>@endif
    @if($whatsAppDigits)<a href="https://wa.me/{{ $whatsAppDigits }}" target="_blank" rel="noopener noreferrer"
        class="mt-4 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#31b879] px-5 font-bold text-white"><x-public.icon
    name="whatsapp" class="size-5" /> Contact us on WhatsApp</a>@endif
</div>