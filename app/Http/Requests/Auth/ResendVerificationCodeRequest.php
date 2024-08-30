<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResendVerificationCodeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^(010|011|012)[0-9]{8}$/'],
        ];
    }


    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must start with 010, 011, or 012 and be exactly 11 digits long.',
        ];
    }

}
