<?php

namespace App\Http\Controllers\Api;

use App\Services\PostService;

class PostApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        PostService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
