<?php


namespace App\Excel;


use App\Excel\Import\PostImport;
use App\Models\Category;
use App\Models\Post;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\PostIndustryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Utils\LogHelper;
use Carbon\Carbon;
use Shuchkin\SimpleCSV;
use Shuchkin\SimpleXLS;
use Shuchkin\SimpleXLSX;

class PostExcelService
{
    const ROW_SUPPORT = 9999999;
    protected $repo_base;
    protected $repo_category;
    protected $repo_sub_category;
    protected $repo_post_industry;

    public function __construct(
        PostRepositoryInterface $repo_base,
        CategoryRepositoryInterface $repo_category,
        SubCategoryRepositoryInterface $repo_sub_category,
        PostIndustryRepositoryInterface $repo_post_industry
    ){
        $this->repo_base            = $repo_base;
        $this->repo_category        = $repo_category;
        $this->repo_sub_category    = $repo_sub_category;
        $this->repo_post_industry   = $repo_post_industry;
    }

    public function import($inputs) {
        if(!isset($inputs['file'])) {
            return ['code' => '003', 'message' => 'File not found'];
        }
        $checkFile = $this->validateFile($inputs['file']);
        if($checkFile['is_failed']) {
            return $checkFile;
        }
        $file_infor = $checkFile['file'];
        $extension = $checkFile['extension'];
        $data = $this->readExcel($file_infor, $extension);
        if($data['is_failed']) {
            return $data;
        }
        return [
            'code' => '200',
            'data' => $data['data']
        ];
    }

    private function validateFile($file) {
        $error = null;
        $code = '999';
        $phpFileUploadErrors = [
            UPLOAD_ERR_OK => 'The file was uploaded',
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Could not write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];

        if (empty($file)) {
            $error = 'No file uploaded or file size exceeds maximum of ';
        } else {
            $extension = $this->getExtesion($file->getClientOriginalName());
            if(!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                $error = 'Khong dung dinh dang file';
                $code = '081';
            }
        }
        if (!empty($file) && array_key_exists('error', $file) && $file['error'] !== UPLOAD_ERR_OK) {
            $error = $phpFileUploadErrors[$file['error']];
        }

        if ($error !== null) {
            LogHelper::writeLog('issued on read file ' . $error, 0);
            return [ 'is_failed' => true, 'code' => $code, 'message' => $error ];
        }
        return [
            'is_failed' => false,
            'file' => $file,
            'extension' => $extension
        ];
    }

    private function readExcel($file, $extension) {
        switch($extension) {
            case 'xlsx':
                return $this->readXLSX($file);
            case 'xls':
                return $this->readXLS($file);
            case 'csv':
                return $this->readCSV($file);
            default:
                return $this->readXLSX($file);
        }
    }

    private function readXLSX($file) {
        if ( $xlsx = SimpleXLSX::parseData(file_get_contents($file)) ) {
            $check_row = $this->validateRow($xlsx->rows());
            if($check_row['is_failed']) {
                return $check_row;
            }
            $check_process = $this->processRow($check_row['rows']);
            if($check_process['is_failed']) {
                return $check_process;
            }
            return [
                'is_failed' => false,
                'data' => $check_process['data']
            ];
        } else {
            return [
                'is_failed' => true,
                'code' => '999',
                'message' => SimpleXLSX::parseError()
            ];
        }
    }

    private function readXLS($file) {
        if ( $xlsx = SimpleXLS::parseData(file_get_contents($file)) ) {
            $check_row = $this->validateRow($xlsx->rows());
            if($check_row['is_failed']) {
                return $check_row;
            }
            $check_process = $this->processRow($check_row['rows']);
            if($check_process['is_failed']) {
                return $check_process;
            }
            return [
                'is_failed' => false,
                'data' => $check_process['data']
            ];
        } else {
            return [
                'is_failed' => true,
                'code' => '999',
                'message' => SimpleXLS::parseError()
            ];
        }
    }

    private function readCSV($file) {
        if ( $csv = SimpleCSV::import(file_get_contents($file), true) ) {
            $check_row = $this->validateRow($csv);
            if($check_row['is_failed']) {
                return $check_row;
            }

            $check_process = $this->processRow($check_row['rows']);
            if($check_process['is_failed']) {
                return $check_process;
            }
            return [
                'is_failed' => false,
                'data' => $check_process['data']
            ];
        } else {
            return [
                'is_failed' => true,
                'code' => '999',
                'message' => 'can not read'
            ];
        }
    }

    private function validateRow($rows) {
        if((count($rows) - 1) > self::ROW_SUPPORT) {
            return [
                'is_failed' => true,
                'code' => '090',
                'message' => 'Vượt quá số dòng cho phép'
            ];
        }
        return [
            'is_failed' => false,
            'rows' => $rows
        ];
    }

    private function processRow($rows) {
        $model = new PostImport();
        $dictCategory = $this->dictCategory();
        $dictSubCategory = $this->dictSubCategory();
        $dictPostIndustry = $this->dictPostIndustry();
        $validate_title = $model->validateTitle($rows[0]);
        if(!$validate_title) {
            return [
                'is_failed' => true,
                'code' => '090',
                'message' => 'Không đúng dữ liệu'
            ];
        }
        $posts = $this->repo_base->all();
        $map_post = [];
        foreach($posts as $pos) {
            if(!isset($map_post[$pos->reference])) {
                $map_post[$pos->reference] = $pos->id;
            }
        }
        $data = $model->formatModels($rows);
        $post_ids = [];
        $insert_posts = [];
        $update_posts = [];

        $insert_res = []; $update_res = [];
        $failed = [];
        foreach($data as $dat) {
            if(isset($dat['reference']) && !empty($dat['reference'])) {
                // update
                if(isset($map_post[$dat['reference']])) {
                    $categoryData = $this->getCategory($dat, $dictCategory);
                    $dat = $categoryData['dat'];
                    $dictCategory = $categoryData['dict'];
                }
            }
        }
    }

    private function getCategory($dat, $dict) {
        if(isset($dat['category_name'])) {
            $category_name = trim($dat['category_name']);
            if(!isset($dict[$category_name]) && !empty($category_name)) {
                $dict = $this->createCategory($category_name, $dict);
            }
            $dat['category_id'] = isset($dict[$category_name]) ? $dict[$category_name] : null;
            $dat['category_name'] = $category_name;
        }
        return [
            'dat' => $dat,
            'dict' => $dict
        ];
    }

    private function createCategory($name, $dict, $type = Category::HOME_OWNER) {
        $now = Carbon::now();
        $pre_fix = config('enums.key_prefix.category') . $now->format(config('enums.key_prefix.format_date')) . (count($dict) + 1);
        $reference = $this->repo_category->getReferenceByPrefix($pre_fix, 'reference',4, true);
        $data = $this->repo_category->create([
            'name' => $name,
            'status' => Category::STATUS_ACTIVE,
            'type' => $type,
            'reference' => $reference
        ]);
        $dict[$data->name] = $data->id;
        return $dict;
    }

    private function dictCategory(){
        $categories = $this->repo_category->all();
        $dict = [];
        foreach($categories as $cate) {
            if(!isset($dict[$cate->name])) {
                $dict[$cate->name] = $cate->id;
            }
        }
        return $dict;
    }

    private function dictSubCategory(){
        $subCategories = $this->repo_sub_category->all();
        $dict = [];
        foreach($subCategories as $sub_cat) {
            if(!isset($dict[$sub_cat->name])) {
                $dict[$sub_cat->name] = $sub_cat->id;
            }
        }
        return $dict;
    }

    private function dictPostIndustry(){
        $postIndustries = $this->repo_post_industry->all();
        $dict = [];
        foreach($postIndustries as $post_industry) {
            if(!isset($dict[$post_industry->name])) {
                $dict[$post_industry->name] = $post_industry->id;
            }
        }
        return $dict;
    }

    private function generateHashTag($inputs) {
        $hash = isset($inputs['hash_tag']) ? $inputs['hash_tag'] : '';
        if(empty($inputs['hash_tag'])){
            $slug = str_slug($inputs['name']);
            $splits = explode('-', $slug);
            $hash = '';
            if(sizeof($splits) == 0){
                $hash = strtolower(mb_substr($inputs['name'], 0, 1, 'UTF-8'));
            } else {
                foreach($splits as $spl){
                    if(!empty($spl)){
                        $hash .= strtolower(mb_substr($spl, 0, 1, 'UTF-8'));
                    }
                }
            }
        }

        return $hash;
    }
}
