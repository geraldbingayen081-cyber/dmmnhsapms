<?php

namespace App\Rules;

use App\Services\SchoolYearService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SequentialSchoolYear implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $error = SchoolYearService::validateSequentialCreation((string) $value);

        if ($error) {
            $fail($error);
        }
    }
}
