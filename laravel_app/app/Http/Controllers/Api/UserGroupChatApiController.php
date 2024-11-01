<?php

namespace App\Http\Controllers\Api;

use App\Services\UserGroupChatService;

class UserGroupChatApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserGroupChatService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
