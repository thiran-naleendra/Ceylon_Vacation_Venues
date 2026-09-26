<?php

namespace App\Http\Requests\Admin\Pages;

use App\Enums\PublicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('publish', $this->route('page')) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::enum(PublicationStatus::class)]];
    }
}
