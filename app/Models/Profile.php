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
        $this->validated_at = Carbon::now();
        $this->save();
        return $this;
    }

    function invalidateProfile(){
        $this->validated_at = null;
        $this->save();
        return $this;
    }

    function validateNCFront(){
        $this->national_card_front_verified_at = Carbon::now();
        $this->save();
        return $this;
    }

    function invalidateNCFront(){
        $this->national_card_front_verified_at = null;
        $this->save();
        return $this;
    }

    function validateNCBack(){
        $this->national_card_back_verified_at = Carbon::now();
        $this->save();
        return $this;
    }

    function invalidateNCBack(){
        $this->national_card_back_verified_at = null;
        $this->save();
        return $this;
    }

    function validateBC(){
        $this->birth_certificate_verified_at = Carbon::now();
        $this->save();
        return $this;
    }

    function invalidateBC(){
        $this->birth_certificate_verified_at = null;
        $this->save();
        return $this;
    }
}
