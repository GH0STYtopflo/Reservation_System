<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\AvailabilityDefType;
use App\Enums\DayOfWeek;
use App\Http\Requests\Availabilities\IndexAvailableSlotsRequest;
use App\Http\Requests\Availabilities\StoreAvailabilityRequest;
use App\Http\Requests\Availabilities\UpdateAvailabilityRequest;
use App\Http\Resources\AvailabilityResource;
use App\Models\Availability;
use App\Models\DoctorProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use RRule\RRule;

class AvailabilityController extends Controller
{
    public function index(DoctorProfile $doctorProfile)
    {
        return $doctorProfile->availabilities->toResourceCollection();
    }


    public function store(StoreAvailabilityRequest $request, DoctorProfile $doctorProfile)
    {
        $data = $request->validated();

        if ($data['type'] == AvailabilityDefType::RECURRING->value) {
            $rstr = "FREQ=WEEKLY;" . "BYDAY=" . implode(",", $data['days_of_week']);

            $rule = new RRule($rstr, "00:00:00"); // cool we have a rrule instance.

            $av = $doctorProfile->availabilities()->make([
                'rrule' => $rule,
                'shift_start_at' => $data['shift_start_at'],
                'shift_end_at' => $data['shift_end_at'],
                'type' => $data['type'],
            ])->refresh();

            if (self::intersects($doctorProfile, $av)) {
                return Response::json(['message' => 'conflicting rules'], 409);
            }

            $av->save();

            return $av->refresh()->toResource();
        } else {
            $array = [];

            foreach ($data['dates'] as $date) {
                $av = $doctorProfile->availabilities()->make([
                    'date' => $date,
                    'shift_start_at' => $data['shift_start_at'],
                    'shift_end_at' => $data['shift_end_at'],
                    'type' => $data['type'],
                ])->refresh();

                if (self::intersects($doctorProfile, $av)) {
                    return Response::json(['message' => 'conflicting rules'], 409);
                }

                $av->save();
                $array[] = $av->refresh();
            }

            return AvailabilityResource::collection($array);
        }
    }

    public static function intersects(DoctorProfile $doctorProfile, Availability $new): bool
    {
        foreach ($doctorProfile->availabilities as $av) {
            $avRecurring = $av->type === AvailabilityDefType::RECURRING;
            $newRecurring = $new->type === AvailabilityDefType::RECURRING;

            if (!($avRecurring || $newRecurring)) {
                if (Carbon::parse($av->date)->format('Y-m-d') !== Carbon::parse($new->date)->format('Y-m-d')) {
                    continue;
                }
            } else {
                $recurring = $avRecurring ? $av : $new;
                $other = $avRecurring ? $new : $av;

                $days = explode(',', explode(';', $recurring->rrule->rfcString())[1]);
                $days[0] = substr($days[0], 6);

                $day = $other->type === AvailabilityDefType::RECURRING
                    ? explode(',', explode(';', $other->rrule->rfcString())[1])
                    : [DayOfWeek::from(Carbon::parse($other->date)->dayOfWeek)->name];

                if ($other->type === AvailabilityDefType::RECURRING) {
                    $day[0] = substr($day[0], 6);
                }

                if (!self::daysIntersect($days, $day)) {
                    continue;
                }
            }

            if (self::hoursIntersect(
                [$av->shift_start_at, $av->shift_end_at],
                [$new->shift_start_at, $new->shift_end_at],
            )) {
                return true;
            }
        }

        return false;
    }
    public static function hoursIntersect(array $h, array $hNew): bool
    {
        return !($hNew[1] < $h[0] || $hNew[0] > $h[1]);
    }

    public static function daysIntersect(array $d, array $dNew): bool
    {
        return !empty(array_intersect($dNew, $d));
    }

    public function show(DoctorProfile $doctorProfile, Availability $availability)
    {
        return $availability->toResource();
    }

    public function update(UpdateAvailabilityRequest $request, DoctorProfile $doctorProfile, Availability $availability)
    {
        $data = $request->validated();

        $availability->fill([
            'shift_start_at' => $data['shift_start_at'] ?? $availability->shift_start_at,
            'shift_end_at' => $data['shift_end_at'] ?? $availability->shift_end_at,
        ]);

        if ($availability->type == AvailabilityDefType::RECURRING) {
            if (!empty($request->validated()['new_days'])) {
                $ruleArr = $availability->rrule->getRule();

                $ruleArr['BYDAY'] = $data['new_days'] ?? $ruleArr['BYDAY'];

                $availability->rrule = new RRule($ruleArr);
            }
        } else {
            $availability->date = $data['new_date'] ?? $availability->date;
        }

        if (self::intersects($doctorProfile, $availability)) {
            return Response::json(['message' => 'conflicting rules'], 409);
        }

        $availability->save();

        return $availability->refresh()->toResource();
    }

    public function destroy(DoctorProfile $doctorProfile, Availability $availability)
    {
        $availability->delete();

        // TODO: Check if the doctor has an appointment which will prevent from deletion

        return Response::noContent();
    }

    public function indexAvailableSlots(IndexAvailableSlotsRequest $request, DoctorProfile $doctorProfile)
    {
        $date = empty($request->validated()['date']) ? now()->setTime(0, 0) : Carbon::parse($request->validated()['date']);

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
            $slots += $this->makeSlots($availability, $date);
        }

        return Response::json([
            "data" => [
                "date" => $date->toAtomString(),
                'slots' => $slots,
            ],
        ]);
    }

    private function makeSlots(Availability $availability, Carbon $date): array
    {
        $start = Carbon::parse($availability->shift_start_at);
        $end = Carbon::parse($availability->shift_end_at);

        $array = [];
        while ($start->lt($end)) {
            if ($this->slotAvailable($date->setTimeFromTimeString($start->toTimeString()), $availability->doctor)) {
                $array[] = $start->toTimeString();
            }

            $start = $start->addMinutes(20);
        }

        return $array;
    }

    private function slotAvailable(Carbon $timestamp, DoctorProfile $doctorProfile): bool
    {
        return !(($doctorProfile->unavailabilities()
                ->whereValueBetween($timestamp->toAtomString(), ['start_at', 'end_at'])
                ->exists()

            ||

            $doctorProfile->appointments()
                ->where('date', $timestamp->toDateString())
                ->whereValueBetween($timestamp->toTimeString(), ['start_at', 'end_at'])
                ->whereNot('status', AppointmentStatus::CANCELLED)
                ->exists()));
    }
}
