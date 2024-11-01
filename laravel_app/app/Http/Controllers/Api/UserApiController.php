<?php


namespace App\Http\Controllers\Api;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        UserService $service_base
    ) {
        $this->service_base = $service_base;
    }

    public function login(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->login($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Login success');
    }

    public function loginWithPassword(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->loginWithPassword($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Login with password success');
    }

    public function loginWithToken(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->loginWithToken($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Login with token success');
    }

    public function verifyOtp(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->verifyOtp($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Verify Otp success');
    }

    public function registerWithPassword(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->registerWithPassword($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Register password success');
    }

    public function forgetPassword(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->forgetPassword($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Forget password success');
    }

    public function verifyResetPassword(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->verifyResetPassword($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Verify reset password success');
    }

    public function changePassword(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->changePassword($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Change password success');
    }

    public function resendOtp(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->resendOtp($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Resend Otp success');
    }

    public function deleteByPassword(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->deleteByPassword($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['message'], 'delete by password success');
    }

    public function logout(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->logout($inputs['data']);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['message'], 'logout success');
    }

    public function checkVersion(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->checkVersion($inputs['data']);
        if($resp['code'] !== '200'){
            $data = isset($resp['data']) ? $resp['data'] : null;
            return $this->sendErrorData($data, $resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['message'], 'Check version success');
    }

    public function updateProfile(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->updateProfile($inputs);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Update profile success');
    }
}
