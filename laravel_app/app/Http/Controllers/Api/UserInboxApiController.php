<?php

namespace App\Http\Controllers\Api;

use App\Services\UserInboxService;

class UserInboxApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserInboxService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
