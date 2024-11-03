<?php

namespace App\Jobs;

use App\Models\UserInbox;
use App\Services\UserInboxService;
use App\Utils\LogHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessSendNotificationInboxJob implements ShouldQueue
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
            DB::beginTransaction();
            $data = $service->sendNotification($this->model);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $service->failedSendNotification($this->model);
            LogHelper::writeLog('issued on process send user inbox notification '  . $e->getMessage() .PHP_EOL. json_encode($e->getTrace()), 0);
        }

        // release job
//        $this->release();
    }
}
