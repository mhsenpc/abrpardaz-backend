<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Profile extends Model
{
    protected $fillable = ['national_card'];

    public function user()
    {
        return $this->hasOne('App\Models\User');
    }
}
