<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Profile extends Model
{
    use SoftDeletes;

    protected $fillable = ['national_card'];

    public function user()
    {
        return $this->hasOne('App\Models\User');
    }
}
