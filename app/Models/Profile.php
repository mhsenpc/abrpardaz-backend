<?php

namespace App\Models;

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
}
