<?php

namespace App\Http\Requests\Admin\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends StoreUserRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->managedUser()) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['email'] = ['required', 'email:rfc', 'max:254', Rule::unique('users', 'email')->ignore($this->managedUser())];
        $rules['password'] = ['nullable', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()];

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->user()?->is($this->managedUser()) && ! $this->boolean('is_active')) {
                $validator->errors()->add('is_active', 'You cannot deactivate your own account.');
            }
        }];
    }

    private function managedUser(): User
    {
        return $this->route('user');
    }
}
