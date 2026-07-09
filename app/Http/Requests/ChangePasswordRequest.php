<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get the custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'The current password is required.',
            'password.required'         => 'A new password is required.',
            'password.min'              => 'The new password must be at least 8 characters.',
            'password.confirmed'        => 'The new password confirmation does not match.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            
            // Check if current password is correct using Hash::check()
            if ($this->filled('current_password') && $user && !Hash::check($this->current_password, $user->password)) {
                $validator->errors()->add('current_password', 'The current password you entered is incorrect.');
            }
        });
    }
}
