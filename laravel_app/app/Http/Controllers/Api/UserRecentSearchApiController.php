<?php

namespace App\Http\Controllers\Api;

use App\Services\UserRecentSearchService;

class UserRecentSearchApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserRecentSearchService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
