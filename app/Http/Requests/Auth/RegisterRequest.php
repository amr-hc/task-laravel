<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^(010|011|012)[0-9]{8}$/', 'unique:users'],
            'password' => 'required|string|min:8',
        ];

    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must start with 010, 011, or 012 and be exactly 11 digits long.',
        ];
    }
}
