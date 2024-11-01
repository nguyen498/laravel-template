<?php

namespace App\Http\Controllers\Api;

use App\Services\UserPostService;

class UserPostApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserPostService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
