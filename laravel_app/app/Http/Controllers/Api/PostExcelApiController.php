<?php

namespace App\Http\Controllers\Api;

use App\Excel\PostExcelService;
use Illuminate\Http\Request;

class PostExcelApiController extends AppBaseController
{
    protected $service_base;

    public function __construct(
        PostExcelService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function import(Request $request){
        $inputs = $request->all();
        $resp = $this->service_base->import($inputs);
        if($resp['code'] !== '200'){
            return $this->sendError($resp['message'], $resp['code']);
        }
        return $this->sendResponse($resp['data'], 'Import success');
    }
}
