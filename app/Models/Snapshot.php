<?php

namespace App\Models;

use App\Scopes\UserIDScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Snapshot extends Model
{
    protected $guarded = ['id'];
    use softDeletes;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserIDScope());
    }
}
