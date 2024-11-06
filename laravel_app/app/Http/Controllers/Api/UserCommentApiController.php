<?php

namespace App\Http\Controllers\Api;

use App\Services\UserCommentService;
use Illuminate\Http\Request;

class UserCommentApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserCommentService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function sendCommentPost(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->sendCommentPost($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Comment post success');
    }

    public function updateComment($id, Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->updateComment($id, $inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Update comment success');
    }

    public function searchApp($id, Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->search($id, $inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Update comment success');
    }
}
