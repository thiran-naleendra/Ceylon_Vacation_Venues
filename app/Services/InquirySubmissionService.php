<?php

namespace App\Services;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Models\WebsiteSetting;
use App\Notifications\NewInquiryNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class InquirySubmissionService
{
    /** @param array<string, mixed> $data */
    public function submit(InquiryType $type, array $data, TourPackage|Vehicle|Property|null $subject = null): Inquiry
    {
        $inquiry = DB::transaction(function () use ($type, $data, $subject): Inquiry {
            $inquiry = new Inquiry;
            $inquiry->fill(Arr::only($data, ['name', 'email', 'phone', 'country_code', 'subject', 'message']));
            $inquiry->forceFill([
                'type' => $type,
                'status' => InquiryStatus::New,
                'privacy_notice_version' => '1.0',
                'privacy_acknowledged_at' => now(),
            ])->save();

            match ($type) {
                InquiryType::Package => $inquiry->packageDetails()->make(Arr::only($data, ['preferred_start_date', 'preferred_end_date', 'adults', 'children']))
                    ->forceFill(['tour_package_id' => $subject->getKey(), 'package_title_snapshot' => $subject->title])->save(),
                InquiryType::Rental => $inquiry->rentalDetails()->make(Arr::only($data, ['pickup_at', 'return_at', 'pickup_location', 'return_location', 'driver_required']))
                    ->forceFill(['vehicle_id' => $subject->getKey(), 'vehicle_category_id' => $subject->vehicle_category_id, 'vehicle_title_snapshot' => $subject->title])->save(),
                InquiryType::Visa => $inquiry->visaDetails()->create(Arr::only($data, ['nationality_code', 'arrival_date', 'current_visa_expiry_date', 'requested_extension_days'])),
                InquiryType::Baggage => $inquiry->baggageDetails()->create(Arr::only($data, ['pickup_location', 'delivery_location', 'pickup_at', 'bag_count', 'estimated_weight_kg', 'special_instructions'])),
                InquiryType::General => null,
                InquiryType::Property => $inquiry->propertyDetails()->make(Arr::only($data, ['check_in_date', 'check_out_date', 'guests']))
                    ->forceFill(['property_id' => $subject->getKey(), 'property_name_snapshot' => $subject->name])->save(),
            };

            return $inquiry;
        });

        $notificationEmail = WebsiteSetting::query()->where('key', 'contact.notification_email')->first()?->value;
        if (is_string($notificationEmail) && filter_var($notificationEmail, FILTER_VALIDATE_EMAIL)) {
            Notification::route('mail', $notificationEmail)->notify(new NewInquiryNotification($inquiry));
        }

        return $inquiry;
    }
}
