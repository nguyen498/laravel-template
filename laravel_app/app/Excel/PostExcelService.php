<?php


namespace App\Excel;


use App\Constants\QueueMap;
use App\Excel\Import\PostImport;
use App\Jobs\CreateKeywordJob;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostIndustry;
use App\Models\PostJob;
use App\Models\PostSale;
use App\Models\SubCategory;
use App\Models\User;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\PostIndustryRepositoryInterface;
use App\Repositories\Interfaces\PostJobRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\PostSaleRepositoryInterface;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Utils\LogHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Shuchkin\SimpleCSV;
use Shuchkin\SimpleXLS;
use Shuchkin\SimpleXLSX;

class PostExcelService
{
    const ROW_SUPPORT = 9999999;
    protected $repo_user;
    protected $repo_base;
    protected $repo_category;
    protected $repo_sub_category;
    protected $repo_post_industry;
    protected $repo_post_sale;
    protected $repo_post_job;

    public function __construct(
        UserRepositoryInterface $repo_user,
        PostRepositoryInterface $repo_base,
        CategoryRepositoryInterface $repo_category,
        SubCategoryRepositoryInterface $repo_sub_category,
        PostIndustryRepositoryInterface $repo_post_industry,
        PostSaleRepositoryInterface $repo_post_sale,
        PostJobRepositoryInterface $repo_post_job
    ){
        $this->repo_user            = $repo_user;
        $this->repo_base            = $repo_base;
        $this->repo_category        = $repo_category;
        $this->repo_sub_category    = $repo_sub_category;
        $this->repo_post_industry   = $repo_post_industry;
        $this->repo_post_job        = $repo_post_job;
        $this->repo_post_sale       = $repo_post_sale;
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
        if (!empty($file) && is_array($file) && (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK)) {
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

    private function getExtesion($name) {
        $str_split = explode('.', $name);
        return $str_split[count($str_split) - 1];
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
        $user = $this->repo_user->findOneBy([
            'reference' => config('enums.admin_reference')
        ]);
        if(!$user) {
            $user = $this->repo_user->create([
                'status' => User::STATUS_UNACTIVE,
                'reference' => config('enums.admin_reference')
            ]);
        }
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
        $posts = $this->repo_base->findAll();
        $map_post = [];
        foreach($posts as $pos) {
            if(!isset($map_post[$pos->reference])) {
                $map_post[$pos->reference] = $pos->id;
            }
        }
        $data = $model->formatModels($rows);
        $post_ids = [];
        $insert_posts = [];
        $insert_sales = [];
        $insert_jobs = [];

        $update_posts = [];
        $update_sales = [];
        $update_jobs = [];

        $insert_res = []; $update_res = [];
        $failed = [];

        $job_fillables = (new PostJob())->getFillable();
        $sale_fillables = (new PostSale())->getFillable();

        $num = count($posts) + 1;
        foreach($data as $dat) {
            $type = $dat['type'];
            if(isset($dat['title']) && !empty($dat['title'])) {
                unset($dat['stt']);
                if(isset($user)) {
                    $dat['user_id'] = $user->id;
                }
                // update
                $categoryData = $this->getCategory($dat, $dictCategory, $dat['type']);
                $dat = $categoryData['dat'];
                $dictCategory = $categoryData['dict'];

                $subCategoryData = $this->getSubCategory($dat, $dictSubCategory, $dat['category_id']);
                $dat = $subCategoryData['dat'];
                $dictSubCategory = $subCategoryData['dict'];

                $subPostIndustry = $this->getPostIndustry($dat, $dictPostIndustry, $dat['sub_category_id']);
                $dat = $subPostIndustry['dat'];
                $dictPostIndustry = $subPostIndustry['dict'];

                if(isset($dat['reference']) && isset($map_post[$dat['reference']])) {
                    $dat['id'] = $map_post[$dat['reference']];
                    if(isset($dat['sale_type']) && !empty($dat['sale_type'])) {
                        $update_sales = $this->setExtentionInputs($dat, $sale_fillables, $update_sales);
                    }

                    if(isset($dat['jb_type']) && !empty($dat['jb_type'])) {
                        $update_jobs = $this->setExtentionInputs($dat, $job_fillables, $update_jobs);
                    }
                    // should remove to prevent post can not update or insert
                    $dat = $this->removeByExtention($dat, $sale_fillables);
                    $dat = $this->removeByExtention($dat, $job_fillables);
                    $dat = $this->removeByExtention($dat, ['sale_type', 'jb_type']);
                    $dat['type'] = $type;
                    $dat['start_date'] = Carbon::now()->toDateTimeString();
                    $update_posts[$map_post[$dat['reference']]] = $dat;
                    // use for update sale, jobs
                    array_push($post_ids, $map_post[$dat['reference']]);
                    array_push($update_res, [
                        'reference' => $dat['reference'],
                        'title' => $dat['title'],
                    ]);
                } else {

                    $dat['id'] = (string) Str::orderedUuid();
                    $dat['reference'] = isset($dat['reference']) ? $dat['reference'] : $this->generateReference($num);
                    if(isset($dat['sale_type']) && !empty($dat['sale_type'])) {
                        $insert_sales = $this->setExtentionInputs($dat, $sale_fillables, $insert_sales);
                    }

                    if(isset($dat['jb_type']) && !empty($dat['jb_type'])) {
                        $insert_jobs = $this->setExtentionInputs($dat, $job_fillables, $insert_jobs);
                    }
                    $dat = $this->removeByExtention($dat, $sale_fillables);
                    $dat = $this->removeByExtention($dat, $job_fillables);
                    $dat = $this->removeByExtention($dat, ['sale_type', 'jb_type']);

                    $dat['type'] = $type;
                    $dat['start_date'] = Carbon::now()->toDateTimeString();
                    array_push($insert_posts, $dat);
                    array_push($insert_res, [
                        'reference' => $dat['reference'],
                        'title' => $dat['title'],
                    ]);
                }
                $num ++;
            } else {
                array_push($failed, [
                    'title' => $dat['title'],
                    'notes' => 'Không có sản phẩm gốc'
                ]);
            }
        }

        if(count($insert_posts) > 0) {
//            Post::query()->insert($insert_posts);
            $this->repo_base->insertDBs($insert_posts);
            // process import for sales
            if(count($insert_sales) > 0) {
                $this->repo_post_sale->insertDBs($insert_sales);
            }
            // process import for jobs
            if(count($insert_jobs) > 0) {
                $this->repo_post_job->insertDBs($insert_jobs);
            }
            $this->processSyncToElastic($insert_posts);
        }


        if(count($update_posts) > 0) {
            $this->repo_base->updateMultiple($update_posts);
            // process import for sales
            if(count($update_sales) > 0) {
                $this->repo_post_sale->deleteByPostIds($post_ids);
                $this->repo_post_sale->insertDBs($update_sales);
            }
            // process import for jobs
            if(count($update_jobs) > 0) {
                $this->repo_post_job->deleteByPostIds($post_ids);
                $this->repo_post_job->insertDBs($update_jobs);
            }
//            Post::query()->upsert($update_posts, 'reference');
            $this->processSyncToElastic($update_posts);
        }

        return [
            'is_failed' => false,
            'data' => [
                'insert' => [
                    'total' => count($insert_posts)
                ],
                'update' => [
                    'total' => count($update_posts),
                    'data' => $update_res
                ],
                'failed' => [
                    'total' => count($failed),
                    'data' => $failed
                ]
            ]
        ];
    }

    private function processSyncToElastic($sync_arrays) {
        foreach ($sync_arrays as $arr){
            if(isset($arr['id'])){
                $post = Post::find($arr['id']);
                $post->searchable();
                $text = "{$arr['title']}. {$arr['description']}";
                dispatch((new CreateKeywordJob($text, $arr['id']))->onQueue(QueueMap::QUEUE_GENERATE_KEYWORD));
            }
        }
    }

    private function removeByExtention($dat, $fillable) {
        foreach ($fillable as $fill) {
            unset($dat[$fill]);
        }
        return $dat;
    }

    private function setExtentionInputs($dat, $fillable, $insert) {
        $inps = [
            'id' => (string) Str::orderedUuid(),
            'post_id' => $dat['id']
        ];
        foreach($dat as $key=>$val) {
            if(($key == 'sale_type' || $key == 'jb_type') && !empty($val)) {
                $inps['type'] = $val;
            }
            if(in_array($key, $fillable)) {
                $inps[$key] = $val;
            }
        }
        array_push($insert, $inps);
        return $insert;
    }

    private function getPostIndustry($dat, $dict, $sub_category_id) {
        if(isset($dat['post_industry_name'])) {
            $post_industry_name = trim($dat['post_industry_name']);
            if(!isset($dict[$post_industry_name]) && !empty($post_industry_name)) {
                $dict = $this->createPostIndustry($post_industry_name, $dict, $sub_category_id);
            }
            $dat['post_industry_id'] = isset($dict[$post_industry_name]) ? $dict[$post_industry_name] : null;
            $dat['post_industry_name'] = $post_industry_name;
        }
        return [
            'dat' => $dat,
            'dict' => $dict
        ];
    }

    private function createPostIndustry($name, $dict, $sub_category_id) {
        $now = Carbon::now();
        $pre_fix = config('enums.key_prefix.post_industry') . $now->format(config('enums.key_prefix.format_date')) . (count($dict) + 1);
        $reference = $this->repo_post_industry->getReferenceByPrefix($pre_fix, 'reference',4, false);
        $data = $this->repo_post_industry->create([
            'name' => $name,
            'status' => PostIndustry::STATUS_ACTIVE,
            'sub_category_id' => $sub_category_id,
            'reference' => $reference
        ]);
        $dict[$data->name] = $data->id;
        return $dict;
    }

    private function getSubCategory($dat, $dict, $category_id) {
        if(isset($dat['sub_category_name'])) {
            $sub_category_name = trim($dat['sub_category_name']);
            if(!isset($dict[$sub_category_name]) && !empty($sub_category_name)) {
                $dict = $this->createSubCategory($sub_category_name, $dict, $category_id);
            }
            $dat['sub_category_id'] = isset($dict[$sub_category_name]) ? $dict[$sub_category_name] : null;
            $dat['sub_category_name'] = $sub_category_name;
        }
        return [
            'dat' => $dat,
            'dict' => $dict
        ];
    }

    private function createSubCategory($name, $dict, $category_id) {
//        $now = Carbon::now();
//        $pre_fix = config('enums.key_prefix.sub_category') . $now->format(config('enums.key_prefix.format_date')) . (count($dict) + 1);
//        $reference = $this->repo_sub_category->getReferenceByPrefix($pre_fix, 'reference',4, false);
        $data = $this->repo_sub_category->create([
            'name' => $name,
            'status' => SubCategory::STATUS_ACTIVE,
            'category_id' => $category_id
        ]);
        $dict[$data->name] = $data->id;
        return $dict;
    }

    private function getCategory($dat, $dict, $type = Category::HOME_OWNER) {
        if(isset($dat['category_name'])) {
            $category_name = trim($dat['category_name']);
            if(!isset($dict[$category_name]) && !empty($category_name)) {
                $dict = $this->createCategory($category_name, $dict, $type);
            }
            $dat['category_id'] = isset($dict[$category_name]) ? $dict[$category_name] : null;
            $dat['category_name'] = $category_name;
        }
        return [
            'dat' => $dat,
            'dict' => $dict
        ];
    }

    private function createCategory($name, $dict, $type) {
        $now = Carbon::now();
        $pre_fix = config('enums.key_prefix.category') . $now->format(config('enums.key_prefix.format_date')) . (count($dict) + 1);
        $reference = $this->repo_category->getReferenceByPrefix($pre_fix, 'reference',4, false);
        $data = $this->repo_category->create([
            'name' => $name,
            'status' => Category::STATUS_ACTIVE,
            'type' => $type,
            'reference' => $reference
        ]);
        $dict[$data->name] = $data->id;
        return $dict;
    }

    private function generateReference($i) {
        $now = Carbon::now();
        $pre_fix = config('enums.key_prefix.post') . $now->format(config('enums.key_prefix.format_date'));
        return $pre_fix . str_pad($i, 6, '0', STR_PAD_LEFT);
//        return $this->repo_base->getReferenceByPrefix($pre_fix, 'reference',6, true);
    }

    private function dictCategory(){
        $categories = $this->repo_category->findAll();
        $dict = [];
        foreach($categories as $cate) {
            if(!isset($dict[$cate->name])) {
                $dict[$cate->name] = $cate->id;
            }
        }
        return $dict;
    }

    private function dictSubCategory(){
        $subCategories = $this->repo_sub_category->findAll();
        $dict = [];
        foreach($subCategories as $sub_cat) {
            if(!isset($dict[$sub_cat->name])) {
                $dict[$sub_cat->name] = $sub_cat->id;
            }
        }
        return $dict;
    }

    private function dictPostIndustry(){
        $postIndustries = $this->repo_post_industry->findAll();
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
