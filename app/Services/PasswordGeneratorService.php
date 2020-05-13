<?php


namespace App\Services;


use Illuminate\Support\Str;

class PasswordGeneratorService
{
    static function generate(int $length = 6)
    {
        return Str::random($length);
    }
}
