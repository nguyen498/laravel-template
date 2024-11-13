<?php

namespace App\Http\Controllers\Api;

use App\Services\ZoneService;
use Illuminate\Http\Request;

class ZoneApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        ZoneService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function getBanners(Request $request) {
        $inputs = $request->all();
        $resp = $this->service_base->getBanners($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Get Banners success');
    }

    public function getBannersV2(Request $request) {
        $inputs = $request->all();
        $resp = $this->service_base->getBanners_v2($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Get Banners success');
    }
}
