<?php

namespace App\Http\Requests\Unavailabilities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnavailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_at' => [Rule::dateTime(), 'after_or_equal:today', 'before_or_equal:end_at'],
            'end_at' => [Rule::dateTime(), 'after_or_equal:today', 'after_or_equal:start_at'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
