<?php

namespace App\Http\Controllers\Api;

use App\Services\UserActionPostService;

class UserActionPostApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserActionPostService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
