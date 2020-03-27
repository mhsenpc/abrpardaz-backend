<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserLimit extends Model
{
    protected $guarded = ['id'];
    protected $table = 'user_limits';

    static function findDefaultGroup(){
        return UserLimit::where('default',true)->first();
    }

    function setAsDefault(){
        UserLimit::where('default',true)->update(['default' => false]);
        UserLimit::where('id',$this->id)->update(['default' => true]);
    }
}
