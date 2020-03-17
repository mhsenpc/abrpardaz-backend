<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $guarded = ['id'];
    protected $table = 'user_groups';

    static function findDefaultGroup(){
        return UserGroup::where('default',true)->first();
    }

    function setAsDefault(){
        UserGroup::where('default',true)->update(['default' => false]);
        UserGroup::where('id',$this->id)->update(['default' => true]);
    }
}
