<?php

namespace App\Http\Controllers\Api;

use App\Services\PostService;
use Illuminate\Http\Request;

class PostApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        PostService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function searchElastic(Request $request){
//        return $this->sendResponse([], 'Search success');

        $inputs = $request->all();
        $resp = $this->service_base->searchElastic($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Search success');
    }
}
