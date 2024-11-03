<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\NotificationService;
use App\Utils\LogHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessSendNotificationOneTimeJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 360;

    protected $model;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        Notification $model
    )
    {
        $this->model            = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(NotificationService $service)
    {
        try {
//            LogHelper::writeLog('it runs job ' . ProcessSendNotificationInboxJob::class, 1);
            DB::beginTransaction();
            $service->processSendUserOneTimeNotification($this->model);
            DB::commit();
//            LogHelper::writeLog('it finished job ' . ProcessSendNotificationInboxJob::class, 1);
        } catch (\Exception $e) {
            DB::rollBack();
            $service->failedSendNotification($this->model);
            LogHelper::writeLog('issued on process send notification ' . $e->getMessage(), 0);
        }
    }
}
