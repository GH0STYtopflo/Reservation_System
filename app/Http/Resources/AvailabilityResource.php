<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $array = [
            'id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'type' => $this->type,
        ];

        if (! empty($this->rrule)) {
            $array['rrule'] = ['rfc' => $this->rrule->rfcString(), 'human_readable' => $this->rrule->humanReadable()];
        }

        if (! empty($this->date)) {
            $array['date'] = $this->date;
        }

        $array['shift_start_at'] = $this->shift_start_at;
        $array['shift_end_at'] = $this->shift_end_at;
        $array['created_at'] = $this->created_at;
        $array['updated_at'] = $this->updated_at;

        return $array;
    }
}
