<?php

namespace App\Http\Controllers\Api;

use App\Services\UserInboxService;
use Illuminate\Http\Request;

class UserInboxApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserInboxService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function updateRead($id, Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->updateRead($id, $inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Update success');
    }

    public function deleteInbox($id){
        $resp = $this->service_base->deleteInbox($id);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Update success');
    }

    public function updateReadAll(){
        $resp = $this->service_base->updateReadAll();
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Update success');
    }

    public function searchApp(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->searchApp($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Search success');
    }
}
