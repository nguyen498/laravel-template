<?php

namespace App\Http\Controllers\Api;

use App\Services\NotificationService;

class NotificationApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        NotificationService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
