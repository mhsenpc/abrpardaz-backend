<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    public function image()
    {
        return $this->belongsTo('App\Models\Image');
    }

    public function plan()
    {
        return $this->belongsTo('App\Models\plan');
    }

    public function sshKey()
    {
        return $this->belongsTo('App\Models\SshKey');
    }
}
