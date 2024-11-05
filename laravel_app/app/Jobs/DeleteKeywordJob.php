<?php

namespace App\Jobs;

use App\Services\Client\KeywordClientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteKeywordJob implements ShouldQueue
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

    protected $id;

    /**
     * Create a new job instance.
     */
    public function __construct( string $id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(KeywordClientService $service): void
    {
        $service->deleteKeywordWithPost([
            'post_id' => $this->id
        ]);
    }
}
