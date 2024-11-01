<?php

namespace App\Http\Controllers\Api;

use App\Services\OneSignals\OneSignalServices;
use Illuminate\Http\Request;

class OneSignalApiController extends AppBaseController
{
    protected $app_id;
    protected $rest_api_key;
    /**
     * @param Request $request
     * @return array
     */
    public function sendAll(Request $request){
        $inputs = $this->getKey($request->all());
        $service = new OneSignalServices($this->app_id, $this->rest_api_key);
        $data = $service->sendNotificationToAll($inputs['data']);
        if($data['code'] != '200'){
            return $this->sendError($data['message'], $data['code']);
        }
        return $this->sendResponse($data['message'], 'Send All successfully');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function sendToUser(Request $request){
        $inputs = $this->getKey($request->all());
        $service = new OneSignalServices($this->app_id, $this->rest_api_key);
        $data = $service->sendNotificationToUser($inputs['data']);
        if($data['code'] != '200'){
            return $this->sendError($data['message'], $data['code']);
        }
        return $this->sendResponse($data['message'], 'Send User successfully');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function sendSegment(Request $request){
        $inputs = $this->getKey($request->all());
        $service = new OneSignalServices($this->app_id, $this->rest_api_key);
        $data = $service->sendNotificationToSegment($inputs['data']);
        if($data['code'] != '200'){
            return $this->sendError($data['message'], $data['code']);
        }
        return $this->sendResponse($data['message'], 'Send Segment successfully');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getNotificationDevice(Request $request){
        $inputs = $this->getKey($request->all());
        $service = new OneSignalServices($this->app_id, $this->rest_api_key);
        $data = $service->getDeviceNotification($inputs['data']);
        if($data['code'] != '200'){
            return $this->sendError($data['message'], $data['code']);
        }
        return $this->sendResponse($data['data'], 'Get Devices successfully');
    }

    private function getKey($inputs) {
        $this->app_id = config('constants.one_signal.app_id');
        $this->rest_api_key = config('constants.one_signal.rest_api_key');
        unset($inputs['signal_type']);
        return $inputs;
    }
}
