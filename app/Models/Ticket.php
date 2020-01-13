<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'ticket_id', 'title', 'priority', 'message', 'status'
    ];

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
        return $this->belongsTo(User::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
