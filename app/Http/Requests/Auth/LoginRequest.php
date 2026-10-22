<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required_without:email', 'alpha_num', 'min:11', 'max:11'],
            'email' => ['required_without:phone_number', 'email'],
            'password' => ['required_without', 'string', 'min:6', 'max:16'],
        ];
    }
}
