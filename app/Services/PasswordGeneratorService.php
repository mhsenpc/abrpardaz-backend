<?php


namespace App\Services;


class PasswordGeneratorService
{
    static function generate(){
        return str_random(8);
    }
}
