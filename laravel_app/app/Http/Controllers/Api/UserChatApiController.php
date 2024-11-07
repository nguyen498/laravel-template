<?php

namespace App\Http\Controllers\Api;

use App\Services\UserChatService;
use Illuminate\Http\Request;

class UserChatApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserChatService $service_base
    )
    {
        $this->service_base         = $service_base;
    }


    public function sendMessage(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->sendMessage($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Send message success');
    }

    public function searchByUser(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->searchApp($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Search success');
    }

    public function deleteMessage($id){
        $resp = $this->service_base->deleteMessage($id);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Delete success');
    }
}
