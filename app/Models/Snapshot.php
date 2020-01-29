<?php

namespace App\Models;

use App\Scopes\UserIDScope;
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

    static function newSnapshot(string $name,int $machine_id,int $user_id,int $image_id){
        return Snapshot::create([
            'name' => $name,
            'machine_id' => $machine_id,
            'user_id' => $user_id,
            'image_id' => $image_id
        ]);
    }

    function updateSizeAndRemoteId(string $remote_id,float $size){
        $this->remote_id = $remote_id;
        $this->size = $size;
        $this->save();
    }
}
