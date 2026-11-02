<?php

namespace App\Http\Requests\Availabilities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAvailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                'in:recurring,once',
            ],
            'days_of_week' => [
                Rule::requiredIf(fn (): bool => $this->type === 'recurring'),
                Rule::prohibitedIf(fn (): bool => $this->type === 'once'),
                'array',
                'min:1',
            ],
            'days_of_week.*' => [
                'required',
                'string',
                'in:MO,TU,WE,TH,FR,SA,SU',
                'distinct',
            ],
            'dates' => [
                Rule::requiredIf(fn (): bool => $this->type === 'once'),
                Rule::prohibitedIf(fn (): bool => $this->type === 'recurring'),
                'array',
                'min:1',
            ],
            'dates.*' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'shift_start_at' => [
                'required',
                'date_format:H:i',
            ],
            'shift_end_at' => [
                'required',
                'date_format:H:i',
                'after:shift_start_at',
            ],
        ];
    }
}
