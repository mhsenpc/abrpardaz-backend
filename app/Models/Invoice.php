<?php

namespace App\Models;

use App\Scopes\UserIDScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserIDScope());
    }
}
