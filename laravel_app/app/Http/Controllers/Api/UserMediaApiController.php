<?php

namespace App\Http\Controllers\Api;

use App\Services\UserMediaService;

class UserMediaApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserMediaService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
