<?php

namespace App\Http\Requests\DoctorProfile;

use App\Role;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'boolean'],
            'education' => ['required', 'string'],

            'specialty_ids' => ['required', 'array'],
            'specialty_ids.*' => ['integer', 'exists:specialties,id', 'distinct'],

            'city_id' => ['required', 'integer', 'exists:cities,id'],

            'user_id' => [
                Rule::requiredIf(fn() => Auth::user()->role === Role::ADMIN),
                Rule::prohibitedIf(fn() => Auth::user()->role !== Role::ADMIN),
                'integer',
                'exists:users,id',
            ],
        ];
    }
}
