<?php

namespace App\Models;

use App\Scopes\UserIDScope;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{
    protected $guarded = ['id'];
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserIDScope());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

}
