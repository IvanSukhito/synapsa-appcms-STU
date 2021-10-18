<?php

namespace App\Console\Commands;

use App\Codes\Logic\PushNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotificationReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification Reminder FCM & Database channel';

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
        Log::info("test");
        (new PushNotification())->checkMeeting(5);
    }

}
