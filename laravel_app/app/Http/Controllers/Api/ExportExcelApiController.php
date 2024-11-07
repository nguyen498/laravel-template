<?php

namespace App\Http\Controllers\Api;

use App\Services\Excel\ExcelExportService;

class ExportExcelApiController
{
    protected $service_base;

    public function __construct(
        ExcelExportService $service_base
    )
    {
        $this->service_base         = $service_base;
    }

    public function getExportFile($path){
        return $this->service_base->getExportFile($path);
    }
}
