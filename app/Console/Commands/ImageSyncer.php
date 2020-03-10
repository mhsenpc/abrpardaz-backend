<?php

namespace App\Console\Commands;

use App\Services\ImageSyncerService;
use Illuminate\Console\Command;

class ImageSyncer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'imports images from OpenStack';

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
        ImageSyncerService::sync();
    }
}
