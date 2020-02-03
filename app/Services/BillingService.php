<?php


namespace App\Services;

use App\Models\Invoice;
use App\Models\Machine;
use App\User;
use Carbon\Carbon;

class BillingService
{
    static function calculate()
    {
        $last_30_days = Carbon::createFromTimestamp(strtotime('-30 days'));
        $users = User::whereNull('last_billing_date')
            ->orWhere('last_billing_date', '<', $last_30_days)
            ->get();

        foreach ($users as $user) {
            $machines = Machine::whereNull('last_billing_date')->orWhere('last_billing_date','<',$last_30_days)->get();
            //calculate vpses
            foreach ($machines as $machine) {
                foreach ($machine->billing as $billing) {
                    $now = Carbon::now();
                    $created_at = new Carbon($billing->created_at);
                    if(empty($billing->end_date)){
                        $hours = $created_at->diffInHours($now);
                        $cost = $hours * $machine->plan->hourly_price;
                    }
                    else{
                        $end_date = new Carbon($billing->end_date);
                        $hours = $created_at->diffInHours($end_date);
                        $cost = $hours * $machine->plan->hourly_price;
                    }

                    echo $user->email . "\n";
                    echo $machine->name . "\n";
                    echo $cost . "\n";
                    $vat  = $cost * 0.9;

                    Invoice::create([
                        'user_id' => $user->id,
                        'amount' => $cost,
                        'vat' => $vat,
                        'total' => $cost+ $vat,
                        'is_paid' => true
                    ]);

                    //$machine->updateLastBillingDate();
                }
            }

            //calculate snapshots

            //calculate volumes

            //$user->updateLastBillingDate();
        }
    }
}
