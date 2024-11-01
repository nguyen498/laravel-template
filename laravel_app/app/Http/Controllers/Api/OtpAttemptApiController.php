<?php

namespace App\Http\Controllers\Api;


use App\Services\OtpAttemptService;

class OtpAttemptApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        OtpAttemptService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
