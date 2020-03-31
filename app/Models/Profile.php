<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use SoftDeletes;

    protected $fillable = ['national_card'];

    public function user()
    {
        return $this->hasOne(\App\User::class);
    }

    function getNameAttribute()
    {
        if (!empty($this->first_name && $this->last_name))
            return $this->first_name . ' ' . $this->last_name;
        else if (!empty($this->first_name))
            return $this->first_name;
        else if (!empty($this->last_name))
            return 'جناب ' . $this->last_name;
        else
            return "کاربر";
    }

    function validateProfile(){
        $this->validation_status = 1;
        $this->save();
        return $this;
    }

    function invalidateProfile(string $reason){
        $this->validation_status = 2;
        $this->validation_reason = $reason;
        $this->save();
        return $this;
    }

    function validateNC(){
        $this->national_code_status = 1;
        $this->save();
        return $this;
    }

    function invalidateNC(string $reason){
        $this->national_code_status = 2;
        $this->national_code_reason = $reason;
        $this->save();
        return $this;
    }

    function validateNCFront(){
        $this->national_card_front_status = 1;
        $this->save();
        return $this;
    }

    function invalidateNCFront(string $reason){
        $this->national_card_front_status = 2;
        $this->national_card_front_reason = $reason;
        $this->save();
        return $this;
    }

    function validateNCBack(){
        $this->national_card_back_status = 1;
        $this->save();
        return $this;
    }

    function invalidateNCBack(string $reason){
        $this->national_card_back_status = 2;
        $this->national_card_back_reason = $reason;
        $this->save();
        return $this;
    }

    function validateBC(){
        $this->birth_certificate_verified_at = Carbon::now();
        $this->save();
        return $this;
    }

    function invalidateBC(string $reason){
        $this->birth_certificate_status = 2;
        $this->birth_certificate_reason = $reason;
        $this->save();
        return $this;
    }
}
