<?php

namespace App\Http\Controllers\Api;

use App\Services\UserSearchService;

class UserSearchApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserSearchService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
