<?php

namespace App\Http\Controllers\Api;

use App\Services\Excel\ExcelExportService;
use Illuminate\Http\Request;

class ExportExcelApiController extends AppBaseController
{
    protected $service_base;

    public function __construct(
        ExcelExportService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function getExportFile($path, Request $request){
        $inputs = $request->all();
        return $this->service_base->getExportFile($inputs, $path);
    }
}
