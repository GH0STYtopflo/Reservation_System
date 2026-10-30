<?php

namespace App\Http\Requests\Availabilities;

use App\Enums\AvailabilityDefType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAvailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'shift_start_at' => [
                'nullable',
                'date',
                'date_format:H:i',
            ],

            'shift_end_at' => [
                'nullable',
                'date',
                'date_format:H:i',
            ],

            'new_date' => [
                'nullable',
                Rule::prohibitedIf(function () {
                    return $this->route('availability')->type === AvailabilityDefType::RECURRING;
                }),
                'date', 'date_format:Y-m-d',
            ],

            'new_days' => [
                'nullable',
                'array',
                'min:1',
                Rule::prohibitedIf(function () {
                    return $this->route('availability')->type === AvailabilityDefType::ONCE;
                }),
            ],
            'new_days.*' => [
                'required',
                'string',
                'in:MO,TU,WE,TH,FR,SA,SU',
            ],
        ];
    }
}
