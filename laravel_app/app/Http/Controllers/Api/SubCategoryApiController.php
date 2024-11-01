<?php

namespace App\Http\Controllers\Api;

use App\Services\SubCategoryService;

class SubCategoryApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        SubCategoryService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
