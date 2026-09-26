<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-settings') ?? false;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+() .-]+$/'],
            'whatsapp_number' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+() .-]+$/'],
            'email' => ['nullable', 'email:rfc', 'max:254'],
            'notification_email' => ['nullable', 'email:rfc', 'max:254'],
            'address' => ['nullable', 'string', 'max:1000'],
            'map_embed_url' => ['nullable', 'url:https', 'max:2048'],
            'footer_content' => ['nullable', 'string', 'max:10000'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('4mb')->dimensions(Rule::dimensions()->minWidth(120)->minHeight(60)->maxWidth(4000)->maxHeight(4000))],
            'favicon' => ['nullable', File::image()->types(['png', 'webp'])->max('2mb')->dimensions(Rule::dimensions()->minWidth(64)->minHeight(64)->maxWidth(1024)->maxHeight(1024))],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
            'social_links' => ['nullable', 'array', 'max:12'],
            'social_links.*.platform' => ['required', 'distinct', Rule::in(['facebook', 'instagram', 'youtube', 'linkedin', 'tiktok', 'x'])],
            'social_links.*.label' => ['nullable', 'string', 'max:100'],
            'social_links.*.url' => ['required', 'url:https', 'max:2048'],
            'social_links.*.is_active' => ['required', 'boolean'],
            'social_links.*.sort_order' => ['required', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
