<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class Billing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing';

    protected $dates = [
        'created_at',
        'updated_at',
        'end_date'
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It generates invoice for users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        BillingService::calculate();
    }
}
