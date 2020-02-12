<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $guarded = ['id'];
    protected $table = 'user_groups';
}
