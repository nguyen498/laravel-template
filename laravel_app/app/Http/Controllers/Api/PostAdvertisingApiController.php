<?php

namespace App\Http\Controllers\Api;

use App\Services\PostAdvertisingService;

class PostAdvertisingApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        PostAdvertisingService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
