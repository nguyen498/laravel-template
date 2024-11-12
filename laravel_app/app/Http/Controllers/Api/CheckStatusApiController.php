<?php


namespace App\Http\Controllers\Api;

class CheckStatusApiController extends AppBaseController
{
    public function getStatus(){
        return $this->sendResponse('OK', 'Server ready');
    }
}
