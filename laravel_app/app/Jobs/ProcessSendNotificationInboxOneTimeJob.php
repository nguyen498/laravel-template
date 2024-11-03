<?php

namespace App\Jobs;

use App\Models\UserInbox;
use App\Services\UserInboxService;
use App\Utils\LogHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessSendNotificationInboxOneTimeJob implements ShouldQueue
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
    public $timeout = 60;

    protected $model;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        UserInbox $model
    )
    {
        $this->model            = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(UserInboxService $service)
    {
        try {
            $service->sendOneTimeNotification($this->model);
        } catch (\Exception $e) {
            LogHelper::writeLog('issued on process send one time user inbox notification ' . $e->getMessage() . ' - ' . json_encode($e->getTrace(), JSON_UNESCAPED_UNICODE) .PHP_EOL. json_encode($e->getTrace()), 0);
        }
        // release job
//        $this->release();
    }
}
