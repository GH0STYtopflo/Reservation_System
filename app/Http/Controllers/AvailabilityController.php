<?php

namespace App\Http\Controllers;

use App\Actions\MakeSlots;
use App\Actions\RuleHasAppointments;
use App\Actions\RulesIntersect;
use App\Enums\AvailabilityDefType;
use App\Http\Requests\Availabilities\IndexAvailableSlotsRequest;
use App\Http\Requests\Availabilities\StoreAvailabilityRequest;
use App\Http\Requests\Availabilities\UpdateAvailabilityRequest;
use App\Http\Resources\AvailabilityResource;
use App\Models\Availability;
use App\Models\DoctorProfile;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use RRule\RRule;

class AvailabilityController extends Controller
{
    public function index(DoctorProfile $doctorProfile)
    {
        Gate::authorize('vc', [Availability::class, $doctorProfile]);

        return $doctorProfile->availabilities->toResourceCollection();
    }

    public function store(StoreAvailabilityRequest $request, DoctorProfile $doctorProfile)
    {
        Gate::authorize('vc', [Availability::class, $doctorProfile]);

        $data = $request->validated();

        if ($data['type'] == AvailabilityDefType::RECURRING->value) {
            $rstr = 'FREQ=WEEKLY;'.'BYDAY=' . implode(',', $data['days_of_week']);

            $rule = new RRule($rstr, '00:00:00'); // cool we have a rrule instance.

            $av = $doctorProfile->availabilities()->make([
                'rrule' => $rule,
                'shift_start_at' => $data['shift_start_at'],
                'shift_end_at' => $data['shift_end_at'],
                'type' => $data['type'],
            ])->refresh();

            if (RulesIntersect::execute($doctorProfile, $av)) {
                return Response::json(['message' => 'conflicting rules'], 409);
            }

            $av->save();

            return \response($av->refresh()->toResource(), 201);
        } else {

            try {
                $array = DB::transaction(function () use ($data, $doctorProfile) {
                    $array = [];

                    foreach ($data['dates'] as $date) {
                        $av = $doctorProfile->availabilities()->make([
                            'date' => $date,
                            'shift_start_at' => $data['shift_start_at'],
                            'shift_end_at' => $data['shift_end_at'],
                            'type' => $data['type'],
                        ])->refresh();

                        if (RulesIntersect::execute($doctorProfile, $av)) {
                            throw new \RuntimeException('conflicting rules');
                        }

                        $av->save();
                        $array[] = $av;
                    }

                    return $array;
                });
            } catch (\RuntimeException $e) {
                return Response::json(['message' => $e->getMessage()], 409);
            }

            return \response(AvailabilityResource::collection($array), 201);
        }
    }

    public function show($doctorProfile, Availability $availability)
    {
        Gate::authorize('vud', $availability);

        return $availability->toResource();
    }

    public function update(UpdateAvailabilityRequest $request, DoctorProfile $doctorProfile, Availability $availability)
    {
        Gate::authorize('vud', $availability);

        $data = $request->validated();

        $availability->fill([
            'shift_start_at' => $data['shift_start_at'] ?? $availability->shift_start_at,
            'shift_end_at' => $data['shift_end_at'] ?? $availability->shift_end_at,
        ]);

        if ($availability->type == AvailabilityDefType::RECURRING) {
            if (! empty($request->validated()['new_days'])) {
                $ruleArr = $availability->rrule->getRule();

                $ruleArr['BYDAY'] = $data['new_days'] ?? $ruleArr['BYDAY'];

                $availability->rrule = new RRule($ruleArr);
            }
        } else {
            $availability->date = $data['new_date'] ?? $availability->date;
        }

        try {
            $availability = DB::transaction(function () use ($availability, $doctorProfile) {
                $availability->delete();

                if (RulesIntersect::execute($doctorProfile, $availability)) {
                    throw new \RuntimeException();
                }

                $availability->save();

                return $availability->refresh();
            });
        } catch (\Throwable) {
            return Response::json(['message' => 'conflicting rules'], 409);
        }

        return $availability->refresh()->toResource();
    }

    public function destroy(DoctorProfile $doctorProfile, Availability $availability)
    {
        Gate::authorize('vud', $availability);

        if (RuleHasAppointments::execute($availability, $doctorProfile)) {
            return Response::json(['message' => 'You have appointments reserved for this availability'], 409);
        }

        $availability->delete();

        return Response::noContent();
    }

    public function indexAvailableSlots(IndexAvailableSlotsRequest $request, DoctorProfile $doctorProfile)
    {
        $date = empty($request->validated()['date']) ? now()->setTime(0, 0) : CarbonImmutable::parse($request->validated()['date']);

        $availabilities = $doctorProfile->availabilities()
            ->where('type', AvailabilityDefType::ONCE)
            ->where('date', $date->toAtomString())
            ->get();


        $availabilities = $availabilities->concat(
            $doctorProfile->availabilities
                ->where('type', AvailabilityDefType::RECURRING)
                ->filter(function (Availability $availability) use ($date) {
                    return $availability->rrule->occursAt($date);
                })
        );

        $slots = [];
        foreach ($availabilities as $availability) {
            $slots += MakeSlots::execute($availability, $date);
        }

        return Response::json([
            'data' => [
                'date' => $date->toAtomString(),
                'slots' => $slots,
            ],
        ]);
    }
}
