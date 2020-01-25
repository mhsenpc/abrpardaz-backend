<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MachineBilling extends Model
{
    protected $guarded = ['id'];
    protected $table = 'machine_billing';

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
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
