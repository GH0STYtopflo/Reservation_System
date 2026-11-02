<?php

namespace App\Http\Requests\Appointment;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'datetime' => [
                'required',
                Rule::dateTime(),
                'after_or_equal:today',
            ],

            'patient_notes' => [
                'nullable',
                'string',
            ],

            'doctor_id' => [
                Rule::requiredIf(function () {
                    return \Auth::user()->role === Role::PATIENT;
                }),
                'exists:doctor_profiles,id',
            ],
        ];
    }
}
