<?php

namespace App\Jobs;

use App\Services\Client\KeywordClientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateKeywordJob implements ShouldQueue
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

    protected $text;
    protected $id;

    /**
     * Create a new job instance.
     */
    public function __construct(mixed $text, string $id)
    {
        $this->text = $text;
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(KeywordClientService $service): void
    {
        $service->createKeywordWithPost([
            'post_id' => $this->id,
            'text' => $this->text
        ]);
    }
}
