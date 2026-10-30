<?php

namespace App\Http\Requests\Availabilities;

use Illuminate\Foundation\Http\FormRequest;

class IndexAvailableSlotsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }
}
