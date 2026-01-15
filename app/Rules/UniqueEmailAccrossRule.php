<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueEmailAcrossRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return !DB::table('users')->where('email', $value)->exists();
    }

    public function message(): string
    {
        return 'Email sudah digunakan, gunakan email lain.';
    }
}
