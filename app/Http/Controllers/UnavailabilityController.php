<?php

namespace App\Http\Controllers;

use App\Actions\IsUnavailabilityValid;
use App\Http\Requests\Unavailabilities\StoreUnavailabilityRequest;
use App\Http\Requests\Unavailabilities\UpdateUnavailabilityRequest;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Unavailability;
use App\Policies\UnavailabilityPolicy;
use Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class UnavailabilityController extends Controller
{
    public function index(DoctorProfile $doctorProfile)
    {
        Gate::authorize('vc', [Unavailability::class, $doctorProfile]);

        return $doctorProfile->unavailabilities;
    }

    public function store(StoreUnavailabilityRequest $request, DoctorProfile $doctorProfile)
    {
        Gate::authorize('vc', [Unavailability::class, $doctorProfile]);

        $data = $request->validated();
        $unav = $doctorProfile->unavailabilities()->make($data);

        if (! IsUnavailabilityValid::execute($unav, $doctorProfile)) {
            return Response::json(['message' => 'Conflicting unavailability definition'], 409);
        }

        $unav->save();

        return $unav->toResource();
    }

    public function show(DoctorProfile $doctorProfile, Unavailability $unavailability)
    {
        Gate::authorize('vud', $unavailability);

        return $unavailability->toResource();
    }

    public function update(UpdateUnavailabilityRequest $request, DoctorProfile $doctorProfile, Unavailability $unavailability)
    {
        Gate::authorize('vud', $unavailability);

        $unavailability->fill($request->validated());

        if (! isUnavailabilityValid::execute($unavailability, $doctorProfile)) {
            return Response::json(['message' => 'Conflicting unavailability definition'], 409);
        }

        $unavailability->save();

        return $unavailability->toResource();
    }

    public function destroy(DoctorProfile $doctorProfile, Unavailability $unavailability)
    {
        Gate::authorize('vud', $unavailability);

        $unavailability->delete();
    }
}
