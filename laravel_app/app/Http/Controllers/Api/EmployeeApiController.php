<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        EmployeeService $service_base
    ) {
        $this->service_base = $service_base;
    }

     public function login(Request $request)
     {
         $res = $this->service_base->loginWithPassword($request->all());
         if ($res['code'] != '200') {
             return $this->sendError($res['message'], $res['code']);
         }
         return $this->sendResponse($res['data'], 'login success');
     }

    public function loginWithToken(Request $request)
    {
        $res = $this->service_base->loginWithToken($request->all());
        if ($res['code'] != '200') {
            return $this->sendError($res['message'], $res['code']);
        }
        return $this->sendResponse($res['data'], 'login success');
    }
}
