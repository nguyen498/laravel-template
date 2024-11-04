<?php

namespace App\Http\Controllers\Api;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaApiController extends BaseApiController
{
    protected $service_base;

    public function __construct(
        MediaService $service_base
    )
    {
        $this->service_base = $service_base;
    }

    public function upload(Request $request){
        $data = $this->service_base->uploadMedia($request);
        if($data['code'] != '200'){
            return $this->sendError($data['message'], $data['code']);
        }
        return $this->sendResponse($data['data'], 'Upload image successfully');
    }

    public function deleteMedia($id)
    {
        $data = $this->service_base->delete($id);
        if($data['code'] != '200'){
            return $this->sendError($data['message'], $data['code']);
        }
        return $this->sendResponse($data['data'], 'Delete image successfully');
    }
}
