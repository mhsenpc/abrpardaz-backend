<?php


namespace App\Services;

use App\Models\Invoice;
use App\Models\Machine;
use App\Models\MachineBilling;
use App\User;
use Carbon\Carbon;

class BillingService
{
    static function calculate()
    {
        $last_30_days = Carbon::createFromTimestamp(strtotime('-30 days'));
        $users = User::where('last_billing_date', '<', $last_30_days)
            ->get();

        foreach ($users as $user) {
            $data = [];
            $total_amount = 0;
            $total_vat = 0;
            echo $user->email . "\n";
            $machines = Machine::where('user_id', $user->id)->
            get();
            //calculate vpses
            foreach ($machines as $machine) {
                echo $machine->id;
     /*           if (!empty($machine->deleted_at))
                    if ($machine->deleted_at->lt($user->last_billing_date))
                        continue;*/
                $billings = MachineBilling::where('machine_id', $machine->id)->
                where('created_at', '>=', $user->last_billing_date)->
                get();
                foreach ($billings as $billing) {
                    echo $billing->id;
                    $now = Carbon::now();
                    $amount = 0;
                    if (empty($billing->end_date)) {
                        $hours = $billing->created_at->diffInHours($now);
                        $amount = $hours * $billing->plan->hourly_price;
                        $total_amount += $amount;
                        $end_date = $now;
                    } else {
                        $end_date = new Carbon($billing->end_date);
                        $hours = $billing->created_at->diffInHours($end_date);
                        $amount = $hours * $billing->plan->hourly_price;
                        $total_amount += $amount;
                    }

                    $vat = $amount * 0.09;
                    $total_vat += $vat;
                    $billing_item = [
                        'title' => 'Server usage period',
                        'type' => 'machine',
                        'start' => $billing->created_at,
                        'end' => $end_date,
                        'hours' => $hours,
                        'amount' => $amount,
                        'vat' => $vat,
                        'total' => $amount + $vat,
                        'machine' => $machine,
                        'plan' => $billing->plan,
                    ];
                    $data[] = $billing_item;
                    //$billing->updateLastBillingDate();
                }

                //$machine->updateLastBillingDate();

            }

            //calculate snapshots

            //calculate backups

            //calculate volumes

            //$user->updateLastBillingDate();

            Invoice::create([
                'user_id' => $user->id,
                'amount' => $total_amount,
                'vat' => $total_vat,
                'total' => $total_amount + $total_vat,
                'is_paid' => false,
                'data' => json_encode($data)
            ]);
        }
    }
}
