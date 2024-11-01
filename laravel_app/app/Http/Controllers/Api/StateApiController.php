<?php

namespace App\Http\Controllers\Api;

use App\Services\StateService;

class StateApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        StateService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
