<?php


namespace App\Services;

use App\User;
use Carbon\Carbon;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Classes\InvoiceItem;

class BillingService
{
    static function calculate()
    {
        $last_30_days = Carbon::createFromTimestamp(strtotime('-30 days'));
        $users = User::whereNull('last_billing_date')
            ->orWhere('last_billing_date', '>', $last_30_days)
            ->with('machines')
            ->get();

        foreach ($users as $user) {
            //calculate vpses
            foreach ($user->machines as $machine) {
                foreach ($machine->billing as $billing) {
                    $created_at = new Carbon($billing->created_at);

                    $last_billing_date = new Carbon($billing->last_billing_date);
                    $now = Carbon::now();
                    $hours = $last_billing_date->diffInHours($now);
                    $price = $hours * $machine->plan->hourly_price;

                    echo $user->email . "\n";
                    echo $machine->name . "\n";
                    echo $price . "\n";

                    //$machine->updateLastBillingDate();
                }
            }

            //calculate snapshots

            //calculate volumes

            //$user->updateLastBillingDate();
        }
    }
}
