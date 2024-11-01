<?php

namespace App\Http\Controllers\Api;

use App\Services\AddressService;

class AddressApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        AddressService $service_base
    )
    {
        $this->service_base = $service_base;
    }
}
