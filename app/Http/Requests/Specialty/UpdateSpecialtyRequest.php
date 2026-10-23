<?php

namespace App\Http\Requests\Specialty;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpecialtyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['string', 'max:255', 'unique:specialties'],
            'description' => ['string'],
        ];
    }
}
