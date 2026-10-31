<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\DoctorProfile\StoreProfileRequest;
use App\Http\Requests\DoctorProfile\UpdateDoctorProfileRequest;
use App\Http\Requests\DoctorProfile\UpdateDoctorSpecialtiesRequest;
use App\Http\Resources\DoctorProfileResource;
use App\Models\DoctorProfile;
use App\Models\User;
use Auth;
use DB;
use Gate;

class DoctorProfileController extends Controller
{
    public function index()
    {
        $doctors = DoctorProfile::get();

        return DoctorProfileResource::collection($doctors);
    }

    public function show(DoctorProfile $doctorProfile)
    {
        return $doctorProfile->load(['doctorSpecialties', 'city'])->toResource();
    }

    public function store(StoreProfileRequest $request)
    {
        $data = $request->validated();
        $currentUser = Auth::user();

        $user = $currentUser->role === Role::ADMIN ? User::findOrFail($data['user_id']) : $currentUser;

        if ($user->doctorProfile()->exists()) {
            return response()->json([
                'message' => 'Profile already exists.',
            ], 409);
        }

        if ($user->role !== Role::DOCTOR) {
            return response()->json([
                'message' => 'Only doctors can have a doctor profile.',
            ], 422);
        }

        $profile = DB::transaction(function () use ($user, $data) {
            $profile = $user->doctorProfile()->make([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'education' => $data['education'],
            ]);

            $profile->city()->associate($data['city_id']);
            $profile->save();

            $profile->doctorSpecialties()->sync(array_values($data['specialty_ids']));

            return $profile->refresh();
        });

        return $profile->toResource();
    }

    public function update(DoctorProfile $doctorProfile, UpdateDoctorProfileRequest $request)
    {
        Gate::authorize('update', $doctorProfile);

        $data = $request->validated();

        $doctorProfile = DB::transaction(function () use ($doctorProfile, $data) {
            $doctorProfile->first_name = $data['first_name'] ?? $doctorProfile->first_name;
            $doctorProfile->last_name = $data['last_name'] ?? $doctorProfile->last_name;
            $doctorProfile->gender = $data['gender'] ?? $doctorProfile->gender;
            $doctorProfile->education = $data['education'] ?? $doctorProfile->education;

            $doctorProfile->city()->associate($data['city_id'] ?? $doctorProfile->city_id);

            $doctorProfile->save();

            return $doctorProfile->refresh()->unsetRelation('user');
        });

        return $doctorProfile->toResource();
    }

    public function updateDoctorSpecialties(DoctorProfile $doctorProfile, UpdateDoctorSpecialtiesRequest $request)
    {
        Gate::authorize('update', $doctorProfile);

        $doctorProfile->doctorSpecialties()->sync(array_values($request->validated()['specialty_ids']));

        return $doctorProfile->load('doctorSpecialties')->unsetRelation('user')->toResource();
    }

    public function verifyDoctor(DoctorProfile $doctorProfile)
    {
        $doctorProfile->update([
            'verified_at' => now()
        ]);


        return $doctorProfile->refresh()->toResource();
    }
}
