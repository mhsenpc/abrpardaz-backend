<?php

namespace App\Models;

use App\Scopes\UserIDScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SshKey extends Model
{
    use softDeletes;

    protected static function boot()
    {
        parent::boot();

        //static::addGlobalScope(new UserIDScope());
    }

    protected $guarded = [];
}
