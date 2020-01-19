<?php

namespace App\Models;

use App\Scopes\UserIDScope;
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

        static::addGlobalScope(new UserIDScope());
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

    static function createMachine(string $name, int $user_id, int $plan_id, int $image_id, $ssh_key_id=null): Machine
    {
        /** @var Machine $machine */
        $machine = new Machine();
        $machine->name = $name;
        $machine->user_id = $user_id;
        $machine->plan_id = $plan_id;
        $machine->image_id = $image_id;
        $machine->ssh_key_id = $ssh_key_id;
        $machine->last_payment_date = Carbon::now();
        $machine->project_id = Auth::user()->getDefaultProject();
        $machine->save();

        return $machine;
    }

    public function updateRemoteID(string $remote_id){
        $this->remote_id = $remote_id;
        $this->save();
        return $this;
    }
}
