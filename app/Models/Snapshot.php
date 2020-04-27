<?php

namespace App\Models;

use App\Scopes\UserIDScope;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Snapshot extends Model
{
    protected $guarded = ['id'];
    use softDeletes;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserIDScope());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'end_date',
        'last_billing_date'
    ];

    static function newSnapshot(string $name,int $machine_id,int $user_id,int $image_id){
        return Snapshot::create([
            'name' => $name,
            'machine_id' => $machine_id,
            'user_id' => $user_id,
            'image_id' => $image_id,
            'last_billing_date' => Carbon::now(),
            'remote_id' => '0'
        ]);
    }

    function updateSizeAndRemoteId(string $remote_id,float $size){
        $this->remote_id = $remote_id;
        $this->size = $size;
        $this->save();
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
