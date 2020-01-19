<?php


namespace App\Services;

use App\Models\Plan;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BillingService
{
    static function calculate(){
        $last_30_days = Carbon::createFromTimestamp(strtotime('-30 days'));
        $users = User::whereNull('last_payment_date')
            ->orWhere('last_payment_date' ,'<',$last_30_days)
            ->get();

        foreach ($users as $user){
            echo $user;
        }
    }
}
