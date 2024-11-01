<?php

namespace App\Http\Controllers\Api;

use App\Services\CountryService;

class CountryApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        CountryService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
