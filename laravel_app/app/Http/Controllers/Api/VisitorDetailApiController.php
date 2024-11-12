<?php

namespace App\Http\Controllers\Api;

use App\Services\VisitorDetailService;
use Illuminate\Http\Request;

class VisitorDetailApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        VisitorDetailService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function setViewClick(Request $request) {
        $inputs = $request->all();
        $resp = $this->service_base->setViewClick($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Set view click success');
    }
}
