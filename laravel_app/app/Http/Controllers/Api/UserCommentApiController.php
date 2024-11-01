<?php

namespace App\Http\Controllers\Api;

use App\Services\UserCommentService;

class UserCommentApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserCommentService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
