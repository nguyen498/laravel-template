<?php

namespace App\Http\Controllers\Api;

use App\Services\AdvertisingRequestService;
use Illuminate\Http\Request;

class AdvertisingRequestApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        AdvertisingRequestService $service_base
    )
    {
        $this->service_base = $service_base;
    }

    public function storeApp(Request $request) {
        $resp = $this->service_base->storeApp($request->all()['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Store success');
    }

    public function confirm(Request $request) {
        $resp = $this->service_base->confirm($request->all());
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Confirm success');
    }
}
