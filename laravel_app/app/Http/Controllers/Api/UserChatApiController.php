<?php

namespace App\Http\Controllers\Api;

use App\Services\UserChatService;

class UserChatApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserChatService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
