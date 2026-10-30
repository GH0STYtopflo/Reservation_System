<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unavailabilities\StoreUnavailabilityRequest;
use App\Http\Requests\Unavailabilities\UpdateUnavailabilityRequest;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Unavailability;
use Illuminate\Support\Facades\Response;

class UnavailabilityController extends Controller
{
    public function index(DoctorProfile $doctorProfile)
    {
        return $doctorProfile->unavailabilities;
    }

    public function store(StoreUnavailabilityRequest $request, DoctorProfile $doctorProfile)
    {
        $data = $request->validated();
        $unav = $doctorProfile->unavailabilities()->make($data);

        if (!self::isValid($unav)) {
            return Response::json(['message' => "Conflicting unavailability definition"], 409);
        }

        $unav->save();

        return $unav->toResource();
    }

    private static function isValid(Unavailability $unavailability): bool
    {
        return !(Appointment::where(
            'start_at',
            '<=',
            $unavailability->end_at
        )
            ->orWhere(
                'end_at',
                '>=',
                $unavailability->start_at
            )
            ->exists());
    }

    public function show(DoctorProfile $doctorProfile, Unavailability $unavailability)
    {
        return $unavailability->toResource();
    }

    public function update(UpdateUnavailabilityRequest $request, DoctorProfile $doctorProfile, Unavailability $unavailability)
    {
        $unavailability->fill($request->validated());

        if (!self::isValid($unavailability)) {
            return Response::json(['message' => "Conflicting unavailability definition"], 409);
        }

        $unavailability->save();

        return $unavailability->toResource();
    }

    public function destroy(DoctorProfile $doctorProfile, Unavailability $unavailability)
    {
        $unavailability->delete();
    }
}
