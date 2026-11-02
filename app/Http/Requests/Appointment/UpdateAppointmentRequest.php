<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'patient_notes' => [
                'nullable',
                'string',
            ],
        ];
    }
}
