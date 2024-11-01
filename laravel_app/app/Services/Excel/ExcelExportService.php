<?php


namespace App\Services\Excel;


use App\Events\Excel\SimpleExtendXLSXGen;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class ExcelExportService
{
    protected $font_size;

    public function __construct() {
        $this->font_size			= env('EXCEL_EXPORT_FONT_SIZE', 12);
    }

    public function getExportFile($path) {
        $path = "/storage/app/private/{$path}";

        $file_path = app()->basePath($path);
        // Kiểm tra xem file có tồn tại trong storage không
        if (file_exists($file_path)) {
            $file_content = File::get($file_path);

            $mimeType = File::mimeType($file_path);

            return (new Response($file_content, 200))->header('Content-Type', $mimeType);
        }
            // Xử lý khi tệp tin không tồn tại
        return response()->json(['message' => 'File not found'], 404);
    }



    public function export($inputs){
        $filename = $inputs['name'] . '_' . time() . '.xlsx';
        $subject = $inputs['subject'];
        $mergeCell = isset($inputs['mergeCell']) ? $inputs['mergeCell'] : [];
        return $this->exportExcel($inputs['data'], $filename, $inputs['column'], $subject, $mergeCell);
    }

    public function exportExcel($datas, $filename, $columns, $subject, $mergeCell)
    {
        $export_arr = [];
        if (isset($datas['selects'])) {
            $column = array_map(function ($item) use ($columns){
                $item = $columns[$item];
                return $item;
            }, $datas['selects']);
            $export_arr = array_merge($column);
        } else {
            foreach($columns as $key => $val) {
                $export_arr[$key] = '<b><style border="#000000" bgcolor="#BCF79C">'. $val .'</style></b>';
            }
        }

        $export_arr = array_merge([$export_arr], $datas);
        $xlsx = $this->setProperties($export_arr, $subject, $this->font_size);
        $path = 'excel_exports';

        if(count($mergeCell) > 0) {
            foreach($mergeCell as $merge) {
                $xlsx->mergeCells($merge);
            }
        }

        $xlsx->saveAsExcel($path, $filename);
        return [
            'code' => '200',
            'data' => env("DOMAIN_URL", "http://localhost:4100") . '/api/v1/exportExcels/export/' . $path . '/' . $filename
        ];
    }

    private function setProperties($export_arr, $subject, $font_size) {
        $xlsx = SimpleExtendXLSXGen::fromArray($export_arr);
        $xlsx->setAuthor('Pogofdev <support@pogofdev.com>')
            ->setCompany('Pogofdev company <support@pogofdev.com>')
            ->setManager('Pogofdev <support@pogofdev.com>')
            ->setTitle($subject)
            ->setSubject($subject)
            ->setDefaultFontSize($font_size);
        return $xlsx;
    }

}
