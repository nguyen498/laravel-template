<?php

namespace App\Http\Controllers\Api;

use App\Services\PostCmsService;

class PostCmsApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        PostCmsService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
