<?php


namespace App\Services;

use App\Models\Invoice;
use App\Models\Machine;
use App\Models\MachineBilling;
use App\Models\Snapshot;
use App\Models\Volume;
use App\Notifications\NewInvoiceNotification;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BillingService
{
    static function calculate()
    {
        $last_30_days = Carbon::createFromTimestamp(strtotime('-30 days'));
        $users = User::where('last_billing_date', '<', $last_30_days)
            ->get();

        $users = User::all();

        foreach ($users as $user) {
            $data = [];
            $total_amount = 0;
            $total_vat = 0;
            list($total_amount, $total_vat, $data) = self::CalculateBillingForMachines($user, $total_amount, $total_vat, $data);
            list($total_amount, $total_vat, $data) = self::CalculateBillingForSnapshots($user, $total_amount, $total_vat, $data);
            list($total_amount, $total_vat, $data) = self::CalculateBillingForVolumes($user, $total_amount, $total_vat, $data);


            $user->updateLastBillingDate();

            if ($total_amount > 0) {
                $invoice = Invoice::create([
                    'user_id' => $user->id,
                    'amount' => $total_amount,
                    'vat' => $total_vat,
                    'total' => $total_amount + $total_vat,
                    'is_paid' => false,
                    'data' => json_encode($data),
                    'invoice_id' => strtoupper(Str::random(10))
                ]);

                $user->notify(new NewInvoiceNotification(Auth::user(), $user->profile, $invoice));
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param $total_amount
     * @param number $total_vat
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public static function CalculateBillingForMachines(\Illuminate\Database\Eloquent\Model $user,float $total_amount, float $total_vat, array $data): array
    {
        //calculate billing for machines
        $machines = Machine::where('user_id', $user->id)
            ->get();
        $machine_last_billing_date = null;
        foreach ($machines as $machine) {
            $billings = MachineBilling::where('machine_id', $machine->id)->orderBy('created_at')
                ->get();
            foreach ($billings as $billing) {
                if (!empty($billing->end_date))
                    if ($billing->last_billing_date->gte($billing->end_date))
                        continue;

                $now = Carbon::now();
                $amount = 0;
                if (empty($billing->end_date)) {
                    //alive billing
                    $hours = $billing->last_billing_date->diffInHours($now);
                    $end_date = $now;
                } else {
                    //dead billing
                    $end_date = new Carbon($billing->end_date);
                    $hours = $billing->last_billing_date->diffInHours($end_date);
                }

                $amount = $hours * $billing->plan->hourly_price;
                $total_amount += $amount;

                $vat = $amount * 0.09;
                $total_vat += $vat;
                if ($amount > 0) {
                    $billing_item = [
                        'title' => 'Server usage period',
                        'type' => 'machine',
                        'start' => $billing->last_billing_date,
                        'end' => $end_date,
                        'hours' => $hours,
                        'amount' => $amount,
                        'vat' => $vat,
                        'total' => $amount + $vat,
                        'machine' => $machine,
                        'plan' => $billing->plan,
                    ];
                    $data[] = $billing_item;
                    $billing->updateLastBillingDate();
                }
                $machine_last_billing_date = $billing->last_billing_date;
            }

            if (empty($machine->deleted_at) && $machine->backup) {
                //calculate machine backup
                $hours = $machine_last_billing_date->diffInHours(Carbon::now());
                $amount = ($hours * $machine->plan->hourly_price) * 0.20; //20% of machine usage price
                $vat = $amount * 0.09;
                $total_vat += $vat;
                $total_amount += $amount;

                //does this machine use auto backup system?
                $billing_item = [
                    'title' => 'Server backup period',
                    'type' => 'backup',
                    'start' => $machine_last_billing_date,
                    'end' => Carbon::now(),
                    'hours' => $hours,
                    'amount' => $amount,
                    'vat' => $vat,
                    'total' => $amount + $vat,
                    'machine' => $machine,
                    'plan' => $machine->plan,
                ];
                $data[] = $billing_item;
            }

        }
        return array($total_amount, $total_vat, $data);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param $total_amount
     * @param number $total_vat
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public static function CalculateBillingForSnapshots(\Illuminate\Database\Eloquent\Model $user, $total_amount, float $total_vat, array $data): array
    {
        $snapshot_hourly_price_per_gig = 5;
        //calculate billing for snapshots
        $snapshots = Snapshot::where('user_id', $user->id)
            ->get();
        foreach ($snapshots as $snapshot) {
            if (!empty($snapshot->end_date))
                if ($snapshot->last_billing_date->gte($snapshot->end_date))
                    continue;

            $now = Carbon::now();
            $amount = 0;
            if (empty($snapshot->end_date)) {
                //alive billing
                $hours = $snapshot->last_billing_date->diffInHours($now);
                $end_date = $now;
            } else {
                //dead billing
                $end_date = new Carbon($snapshot->end_date);
                $hours = $snapshot->last_billing_date->diffInHours($end_date);
            }

            $amount = $hours * $snapshot->size * $snapshot_hourly_price_per_gig;
            $total_amount += $amount;

            $vat = $amount * 0.09;
            $total_vat += $vat;
            if ($amount > 0) {
                $billing_item = [
                    'title' => 'snapshot usage period',
                    'type' => 'snapshot',
                    'start' => $snapshot->last_billing_date,
                    'end' => $end_date,
                    'hours' => $hours,
                    'amount' => $amount,
                    'vat' => $vat,
                    'total' => $amount + $vat,
                    'snapshot' => $snapshot,
                ];
                $data[] = $billing_item;
                $snapshot->updateLastBillingDate();
            }

        }
        return array($total_amount, $total_vat, $data);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param $total_amount
     * @param number $total_vat
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public static function CalculateBillingForVolumes(\Illuminate\Database\Eloquent\Model $user, $total_amount, float $total_vat, array $data): array
    {
        $volume_hourly_price_per_gig = 5;
        //calculate billing for snapshots
        $volumes = Volume::where('user_id', $user->id)
            ->get();
        foreach ($volumes as $volume) {
            if($volume->is_root)
                continue;

            if (!empty($volume->end_date))
                if ($volume->last_billing_date->gte($volume->end_date))
                    continue;

            $now = Carbon::now();
            $amount = 0;
            if (empty($volume->end_date)) {
                //alive billing
                $hours = $volume->last_billing_date->diffInHours($now);
                $end_date = $now;
            } else {
                //dead billing
                $end_date = new Carbon($volume->end_date);
                $hours = $volume->last_billing_date->diffInHours($end_date);
            }

            $amount = $hours * $volume->size * $volume_hourly_price_per_gig;
            $total_amount += $amount;

            $vat = $amount * 0.09;
            $total_vat += $vat;
            if ($amount > 0) {
                $billing_item = [
                    'title' => 'volume usage period',
                    'type' => 'volume',
                    'start' => $volume->last_billing_date,
                    'end' => $end_date,
                    'hours' => $hours,
                    'amount' => $amount,
                    'vat' => $vat,
                    'total' => $amount + $vat,
                    'volume' => $volume,
                ];
                $data[] = $billing_item;
                $volume->updateLastBillingDate();
            }
        }
        return array($total_amount, $total_vat, $data);
    }
}
