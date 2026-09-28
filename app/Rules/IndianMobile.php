<?php

namespace App\Rules;

use App\Support\IndianPhone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IndianMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        if (! IndianPhone::isValid((string) $value)) {
            $fail('Enter a valid Indian mobile number with +91 (10 digits starting with 6–9).');
        }
    }
}
