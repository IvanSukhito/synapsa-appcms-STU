<?php

namespace App\Console\Commands;

use App\Codes\Logic\PushNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoFinishMeeting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:finishMeeting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Finish Meeting if user forgot';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        (new PushNotification())->checkMeeting(5);
    }

}
