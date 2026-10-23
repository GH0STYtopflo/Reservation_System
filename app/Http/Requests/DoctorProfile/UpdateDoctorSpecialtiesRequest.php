<?php

namespace App\Http\Requests\DoctorProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorSpecialtiesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'specialty_ids' => ['required', 'array'],
            'specialty_ids.*' => ['required', 'integer', 'distinct', 'exists:specialties,id'],
        ];
    }
}
