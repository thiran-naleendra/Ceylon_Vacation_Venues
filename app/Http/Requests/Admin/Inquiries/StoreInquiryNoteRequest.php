<?php

namespace App\Http\Requests\Admin\Inquiries;

use Illuminate\Foundation\Http\FormRequest;

class StoreInquiryNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('inquiry')) ?? false;
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:5000']];
    }
}
