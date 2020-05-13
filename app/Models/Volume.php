<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Volume extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'end_date',
        'last_billing_date'
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function project()
    {
        return $this->belongsTo(Machine::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    function updateLastBillingDate(){
        $this->last_billing_date = Carbon::now();
        $this->save();
        return $this;
    }

    function stopBilling(){
        $this->end_date = Carbon::now();
        $this->save();
        return $this;
    }
}
