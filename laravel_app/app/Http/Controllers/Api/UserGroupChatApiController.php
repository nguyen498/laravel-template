<?php

namespace App\Http\Controllers\Api;

use App\Services\UserGroupChatService;
use Illuminate\Http\Request;

class UserGroupChatApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserGroupChatService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function searchByUser(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->searchApp($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Search success');
    }

    public function deleteGroupChat($id){
        $resp = $this->service_base->deleteGroupChat($id);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Delete success');
    }
}
