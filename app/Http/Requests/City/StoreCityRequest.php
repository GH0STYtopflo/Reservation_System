<?php

namespace App\Http\Requests\City;

use App\Role;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCityRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Auth::user()->role === Role::ADMIN ? Response::allow() : Response::denyAsNotFound();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'unique:cities', 'max:255'],
        ];
    }
}
