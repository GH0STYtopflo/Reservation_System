<?php

namespace App\Http\Requests\DoctorProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'gender' => ['boolean'],
            'education' => ['string'],

            'specialty_ids' => ['array'],
            'specialty_ids.*' => ['integer', 'exists:specialties,id', 'distinct'],

            'city_id' => ['integer', 'exists:cities,id'],
        ];
    }
}
