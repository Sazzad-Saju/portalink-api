<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueUsername implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $customerExists = DB::table('customers')->where('username', $value)->exists();
        $adminExists = DB::table('admins')->where('username', $value)->exists();
        
        if ($customerExists || $adminExists) {
            $fail('The ' . $attribute . ' must be unique!');
        }
    }
}
