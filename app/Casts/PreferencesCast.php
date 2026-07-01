<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PreferencesCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded) || array_is_list($decoded)) {
            return [];
        }

        return $decoded;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return json_encode(new \stdClass());
        }

        if ($value instanceof \stdClass) {
            $value = (array) $value;
        }

        if (is_array($value) && array_is_list($value)) {
            return json_encode(new \stdClass());
        }

        return json_encode((object) ($value ?: []));
    }
}
