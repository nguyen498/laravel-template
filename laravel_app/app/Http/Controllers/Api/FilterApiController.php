<?php

namespace App\Http\Controllers\Api;

use App\Services\FilterService;

class FilterApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        FilterService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
