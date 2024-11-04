<?php

namespace App\Http\Controllers\Api;

use App\Services\PostIndustryService;

class PostIndustryApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        PostIndustryService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
