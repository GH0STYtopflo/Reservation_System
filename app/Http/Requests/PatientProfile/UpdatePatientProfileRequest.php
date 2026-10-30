<?php

namespace App\Http\Requests\PatientProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'gender' => ['boolean'],
            'birth_date' => ['date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'blood_type' => ['in:A+,A-,B+,B-,AB+,AB-,O+,O-', 'max:255'],
            'city_id' => ['exists:cities,id'],
        ];
    }
}
