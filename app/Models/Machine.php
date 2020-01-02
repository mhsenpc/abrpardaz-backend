<?php

namespace App\Models;

use App\Scopes\UserIDScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use softDeletes;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserIDScope());
    }

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
