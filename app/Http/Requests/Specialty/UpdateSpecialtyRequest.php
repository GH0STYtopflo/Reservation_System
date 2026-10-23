<?php

namespace App\Http\Requests\Specialty;

use App\Models\Specialty;
use Auth;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSpecialtyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['string', 'max:255', 'unique:specialties'],
            'description' => ['string'],
        ];
    }
}
