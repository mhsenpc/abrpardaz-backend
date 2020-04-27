<?php

namespace App\Models;

use App\Scopes\ProjectAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function users()
    {
        return $this->belongsToMany(\App\User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ProjectAccessScope());
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}
