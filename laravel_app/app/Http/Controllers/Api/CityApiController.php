<?php

namespace App\Http\Controllers\Api;

use App\Services\CityService;

class CityApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        CityService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
