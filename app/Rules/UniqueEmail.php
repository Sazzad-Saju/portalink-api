<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UniqueEmail implements ValidationRule
{
    protected $ignoreId; 
    public function __construct($ignoreId = null)
    {
        $this->ignoreId = $ignoreId;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $adminExists = DB::table('admins')
                    ->when($this->ignoreId, function ($query) {
                        $query->where('id', '!=', $this->ignoreId);
                    })
                    ->where('email', $value)
                    ->exists();
                    
        $customerExists = DB::table('customers')
                            ->where('email', $value)
                            ->exists();
        
        
        if ($customerExists && $adminExists) {
            $fail('The ' . $attribute . ' must be unique!');
        }
    }
}
