<?php


namespace App\Services;


use Illuminate\Support\Str;

class PasswordGeneratorService
{
    static function generate(){
        return Str::random(6);
    }
}
