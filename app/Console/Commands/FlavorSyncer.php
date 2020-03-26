<?php

namespace App\Console\Commands;

use App\Services\FlavorSyncerService;
use Illuminate\Console\Command;

class FlavorSyncer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flavor:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'imports flavors';

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
        (new FlavorSyncerService())->sync();
    }
}
