<?php

namespace App\Http\Controllers\Api;

use App\Services\FilterDetailService;

class FilterDetailApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        FilterDetailService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
