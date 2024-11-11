<?php

namespace App\Jobs;

use App\Services\PostService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncFilterElasticsearch implements ShouldQueue
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
    /**
     * Create a new job instance.
     */

    protected mixed $data;

    public function __construct(
        mixed $data
    )
    {
        $this->data            = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(PostService $service): void
    {
        $service->syncFilterElasticsearch($this->data);
    }
}
