<?php

namespace App\Http\Controllers\Api;

use App\Services\PostCmsService;
use Illuminate\Http\Request;

class PostCmsApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        PostCmsService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function exportExcel(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->exportExcel($inputs);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Search success');
    }
}
