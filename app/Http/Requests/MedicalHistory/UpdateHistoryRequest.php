<?php

namespace App\Http\Requests\MedicalHistory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHistoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['string', 'max:255'],
            'description' => ['string'],
            'type' => ['string', 'in:condition,surgery,medication'],
        ];
    }
}
