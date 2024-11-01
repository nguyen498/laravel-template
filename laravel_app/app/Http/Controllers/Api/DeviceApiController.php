<?php

namespace App\Http\Controllers\Api;

use App\Services\DeviceService;

class DeviceApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        DeviceService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
