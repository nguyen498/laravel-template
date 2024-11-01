<?php

namespace App\Http\Controllers\Api;

use App\Services\FeedbackService;

class FeedbackApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        FeedbackService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
