<?php

namespace App\Http\Requests\Admin\Redirects;

use App\Models\Redirect;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Redirect::class) ?? false;
    }

    public function rules(): array
    {
        return ['source_path' => ['required', 'string', 'max:512', 'regex:~^/(?!/)(?!.*[?#\\\\\s])(?:[^/]+/)*[^/]*$~', Rule::unique('redirects', 'source_path')], 'destination_path' => ['required', 'string', 'max:512', 'different:source_path', 'regex:~^/(?!/)(?!.*[?#\\\\\s])(?:[^/]+/)*[^/]*$~'], 'status_code' => ['required', 'integer', Rule::in([301, 302, 307, 308])], 'is_active' => ['required', 'boolean']];
    }
}
