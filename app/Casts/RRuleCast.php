<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use RRule\RRule;

class RRuleCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value == null ? null : RRule::createFromRfcString($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value == null ? null : $value->rfcString();
    }
}
