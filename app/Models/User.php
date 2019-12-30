<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    public function profile()
    {
        return $this->belongsTo('App\Models\Profile');
    }

    public function project()
    {
        return $this->belongsToMany('App\Models\Project');
    }
}
