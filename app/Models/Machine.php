<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Machine extends Model
{
    use softDeletes;

    protected static function boot()
    {
        parent::boot();

        //static::addGlobalScope(new UserIDScope());
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function sshKey()
    {
        return $this->belongsTo(SshKey::class);
    }

    public function billing()
    {
        return $this->hasMany(MachineBilling::class);
    }

    static function createMachine(string $name, int $user_id, int $plan_id, int $image_id, $ssh_key_id = null): Machine
    {
        /** @var Machine $machine */
        $machine = new Machine();
        $machine->name = $name;
        $machine->user_id = $user_id;
        $machine->plan_id = $plan_id;
        $machine->image_id = $image_id;
        $machine->ssh_key_id = $ssh_key_id;
        $machine->project_id = Auth::user()->getDefaultProject()->id;
        print_r($machine->project_id);
        $machine->save();

        return $machine;
    }

    public function updateRemoteID(string $remote_id)
    {
        $this->remote_id = $remote_id;
        $this->save();
        return $this;
    }

    function changePlan($newPlan)
    {
        $billing = MachineBilling::where('machine_id', $this->id)->whereNull('end_date')->first();
        $billing->stopBilling();
        $this->plan_id = $newPlan->id;
        $this->save();
        MachineBilling::create([
            'machine_id' => $this->id,
            'plan_id' => $newPlan->id,
            'last_billing_date' => Carbon::now()
        ]);
    }
}
