<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientProfile\StorePatientProfileRequest;
use App\Http\Requests\PatientProfile\UpdatePatientProfileRequest;
use App\Models\PatientProfile;
use App\Models\User;
use Auth;
use Gate;

class PatientProfileController extends Controller
{
    public function index()
    {
        return PatientProfile::get()->toResourceCollection();
    }

    public function store(StorePatientProfileRequest $request)
    {
        $data = $request->validated();

        $user = empty($data['user_id']) ? Auth::user() : User::firstWhere('id', $data['user_id']);

        if ($user->patientProfile()->exists()) {
            return response()->json([
                'message' => 'Profile already exists.',
            ], 409);
        }

        $profile = $user->patientProfile()->create($data);

        return $profile->refresh()->load('city')->toResource();
    }

    public function show(PatientProfile $patientProfile)
    {
        Gate::authorize('view', $patientProfile);

        return $patientProfile->load('city')->unsetRelation('user')->toResource();
    }

    public function update(UpdatePatientProfileRequest $request, PatientProfile $patientProfile)
    {
        Gate::authorize('update', $patientProfile);

        $data = $request->validated();

        $patientProfile->update([
            'first_name' => $data['first_name'] ?? $patientProfile->first_name,
            'last_name' => $data['last_name'] ?? $patientProfile->last_name,
            'gender' => $data['gender'] ?? $patientProfile->gender,
            'birth_date' => $data['birth_date'] ?? $patientProfile->birth_date,
            'blood_type' => $data['blood_type'] ?? $patientProfile->blood_type,
            'city_id' => $data['city_id'] ?? $patientProfile->city_id,
        ]);

        return $patientProfile->load('city')->refresh()->toResource();
    }
}
