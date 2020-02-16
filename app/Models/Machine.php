<?php

namespace App\Models;

use App\Scopes\UserIDScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use softDeletes;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserIDScope());
    }

    protected $hidden = ['password'];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function sshKey()
    {
        return $this->belongsTo(SshKey::class);
    }

    public function billing()
    {
        return $this->hasMany(MachineBilling::class);
    }

    static function createMachine(string $name, int $user_id, int $plan_id, int $image_id, int $project_id, $ssh_key_id = null): Machine
    {
        /** @var Machine $machine */
        $machine = new Machine();
        $machine->name = $name;
        $machine->user_id = $user_id;
        $machine->plan_id = $plan_id;
        $machine->image_id = $image_id;
        $machine->ssh_key_id = $ssh_key_id;
        $machine->project_id = $project_id;
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

    function enableBackup()
    {
        $this->backup = true;
        $this->save();
        return $this;
    }

    function disableBackup()
    {
        $this->backup = false;
        $this->save();
        return $this;
    }
}
