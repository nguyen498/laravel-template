<?php


namespace App\Http\Controllers\Api;

use App\Services\RoleService;

class RoleApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        RoleService $service_base
    )
    {
        $this->service_base         = $service_base;
    }
}
