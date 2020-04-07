<?php

namespace App\Models;

use App\Scopes\TicketUserIDScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TicketUserIDScope());
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function latestReply()
    {
        return $this->hasOne(Reply::class)->latest();
    }
}
