<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use App\Utils\LogHelper;
use Illuminate\Console\Command;

class ProcessSendSystemNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:process_send_system_notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process send system notification to user and employee';

    /**
     * Execute the console command.
     */

    protected $service_notification;

    public function __construct(
        NotificationService $service_notification
    )
    {
        parent::__construct();
        $this->service_notification = $service_notification;
    }

    public function handle()
    {
        try {
//            LogHelper::writeLog('Start ' . ProcessSendSystemNotification::class, 1);
            $this->service_notification->processSendSystemNotification();
//            LogHelper::writeLog('End ' . ProcessSendSystemNotification::class, 1);
        }catch (\Exception $e){
            LogHelper::writeLog('issued on send notification system ' . $e->getMessage(), 0);
        }
    }
}
