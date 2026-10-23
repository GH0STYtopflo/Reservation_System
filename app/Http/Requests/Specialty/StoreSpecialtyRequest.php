<?php

namespace App\Http\Requests\Specialty;

use App\Models\Specialty;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSpecialtyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:specialties'],
            'description' => ['required', 'string'],
        ];
    }
}
