<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Volume extends Model
{
    use SoftDeletes;

    public function machine()
    {
        return $this->belongsTo('App\Models\Machine');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}