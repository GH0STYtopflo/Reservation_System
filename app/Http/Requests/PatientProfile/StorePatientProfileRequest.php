<?php

namespace App\Http\Requests\PatientProfile;

use App\Enums\Role;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'boolean'],
            'birth_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'blood_type' => ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-', 'max:255'],
            'city_id' => ['required', 'exists:cities,id'],
            'user_id' => [
                Rule::prohibitedIf(function () {
                    return Auth::user()->role !== Role::ADMIN;
                }),
                Rule::requiredIf(function () {
                return Auth::user()->role === Role::ADMIN;
            })]
        ];
    }
}
