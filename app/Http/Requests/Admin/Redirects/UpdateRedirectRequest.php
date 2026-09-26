<?php

namespace App\Http\Requests\Admin\Redirects;

use Illuminate\Validation\Rule;

class UpdateRedirectRequest extends StoreRedirectRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('redirect')) ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['source_path'][4] = Rule::unique('redirects', 'source_path')->ignore($this->route('redirect'));

        return $rules;
    }
}
