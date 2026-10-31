<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RescheduleAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'datetime' => [
                'required',
                Rule::dateTime(),
                'after_or_equal:today'
            ],
        ];
    }
}
