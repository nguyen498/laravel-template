<?php

namespace App\Http\Controllers\Api;

use App\Services\ConfigService;

class ConfigApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        ConfigService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
