<?php

namespace App\Http\Controllers\Api;

use App\Services\AdvertisingRequestService;

class AdvertisingRequestApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        AdvertisingRequestService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
