<?php

namespace App\Http\Controllers\Api;

use App\Services\MediaService;

class MediaApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        MediaService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
