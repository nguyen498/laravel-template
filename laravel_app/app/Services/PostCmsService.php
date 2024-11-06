<?php

namespace App\Services;

use App\Jobs\CreateKeywordJob;
use App\Jobs\DeleteKeywordJob;
use App\Models\Post;
use App\Models\PostCms;
use App\Repositories\Interfaces\PostCmsRepositoryInterface;
use App\Repositories\Interfaces\PostIndustryRepositoryInterface;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Services\Base\BaseService;
use App\Services\Excel\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostCmsService extends BaseService
{
    protected $repo_base;
    protected $repo_sub_category;
    protected $repo_post_industry;
    protected $service_export_excel;
    protected $with;

    public function __construct(
        PostCmsRepositoryInterface $repo_base,
        SubCategoryRepositoryInterface $repo_sub_category,
        PostIndustryRepositoryInterface $repo_post_industry,
        ExcelExportService $service_export_excel
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_sub_category = $repo_sub_category;
        $this->repo_post_industry = $repo_post_industry;
        $this->service_export_excel = $service_export_excel;
        $this->with = ['category', 'subCategory', 'postIndustry'];
    }

    public function getModelName()
    {
        return 'Post';
    }

    public function getTableName()
    {
        return (new PostCms())->getTable();
    }

    public function store($inputs)
    {
        $user = Auth::user();
        $validate = $this->checkInputs($inputs, null);
        if ($validate['is_failed']) {
            return $validate;
        }
        $input_data = $validate['inputs'];
        $input_data['user_id'] = $user->id;

        $data = $this->repo_base->create($input_data);
        $data = $this->repo_base->findById($data->id, $this->with);

        $text = "{$data->title}. {$data->description}";
        return [
            'code' => '200',
            'data' => $this->formatData($data)
        ];
    }

    public function update($id, $inputs)
    {
        $user = Auth::user();
        $data = $this->repo_base->findById($id);
        if (!isset($data)) {
            return ['code' => '004', 'message' => $this->getModelName()];
        }
        $validate = $this->checkInputs($inputs, $id);
        if ($validate['is_failed']) {
            return $validate;
        }
        $input_data = $validate['inputs'];
        if($user->id !== $data->user_id){
            return [
                'code' => '008',
                'message' => 'User'
            ];
        }

        $this->repo_base->update($id, $input_data);
        $data = $this->repo_base->findById($data->id, $this->with);
        $text = "{$data->title}. {$data->description}";
        dispatch(new CreateKeywordJob($text, $data->id));
        return [
            'code' => '200',
            'data' => $this->formatData($data)
        ];
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $data = $this->repo_base->findById($id);

        if (!isset($data)) {
            return ['code' => '004', 'message' => $this->getModelName()];
        }

        if($user->id !== $data->user_id){
            return [
                'code' => '008',
                'message' => 'User'
            ];
        }

        dispatch(new DeleteKeywordJob($data->id));
        $data->delete();

        return [
            'code' => '200',
            'message' => 'Deleted successfully'
        ];
    }

    public function checkInputs($inputs, $id)
    {
        $data = [];

        if(!isset($inputs['store_name'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'store name'
            ];
        }
        $data['store_name'] = $inputs['store_name'];
        if(!isset($inputs['store_address'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'store address'
            ];
        }
        $data['store_address'] = $inputs['store_address'];
        if(!isset($inputs['lng'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'longitude'
            ];
        }
        $data['lng'] = $inputs['lng'];
        if(!isset($inputs['lat'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'latitude'
            ];
        }
        $data['lat'] = $inputs['lat'];

        if(isset($data['lat']) && $data['lng']){
            $data['location'] = [
                'lon' => $data['lng'],
                'lat' => $data['lat']
            ];
        }

        if(!isset($inputs['title'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'title'
            ];
        }
        $data['title'] = $inputs['title'];

        if(!isset($inputs['phone_number'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'phone'
            ];
        }
        $data['phone_number'] = $inputs['phone_number'];

        if(!isset($inputs['email'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'email'
            ];
        }
        $data['email'] = $inputs['email'];

        if(!isset($inputs['website'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'website'
            ];
        }
        $data['website'] = $inputs['website'];

        if(!isset($inputs['medias'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'medias'
            ];
        }
        $data['medias'] = json_encode($inputs['medias']);
        if(!isset($inputs['sub_category_id'])) {
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'sub category'
            ];
        }
        $data['sub_category_id'] = $inputs['sub_category_id'];

        $sub_category = $this->repo_sub_category->findById($data['sub_category_id'], ['category']);
        if(!isset($sub_category)){
            return [
                'is_failed' => true,
                'code' => '004',
                'message' => 'Sub category'
            ];
        }

        $data['sub_category_name'] = $sub_category->name;
        $data['category_id'] = $sub_category->category->id;
        $data['category_name'] = $sub_category->category->name;

        $post_industry = $this->repo_post_industry->findOneBy([
            'sub_category_id' => $data['sub_category_id']
        ]);
        if(isset($post_industry)){
            $data['post_industry_id'] = $post_industry->id;
            $data['post_industry_name'] = $post_industry->title;
        }

        if(!isset($inputs['type'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'type'
            ];
        }

        $data['type'] = $inputs['type'];

        if($inputs['type'] === Post::TYPE_TUYEN_DUNG){
            if(!isset($inputs['work_position'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Work position'
                ];
            }
            $data['work_position'] = $inputs['work_position'];
            if(!isset($inputs['avg_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Average salary'
                ];
            }
            $data['avg_salary'] = $inputs['avg_salary'];
            if(!isset($inputs['min_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Min salary'
                ];
            }
            $data['min_salary'] = $inputs['min_salary'];
            if(!isset($inputs['max_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Max salary'
                ];
            }
            $data['max_salary'] = $inputs['max_salary'];
            if(!isset($inputs['job_type'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job type'
                ];
            }
            $data['job_type'] = $inputs['job_type'];
            if(!isset($inputs['job_contract'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job contract'
                ];
            }
            $data['job_contract'] = json_encode($inputs['job_contract'], JSON_UNESCAPED_UNICODE);
            if(!isset($inputs['job_time'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job time'
                ];
            }
            $data['job_time'] = json_encode($inputs['job_time']);
            if(!isset($inputs['job_experience'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job experience'
                ];
            }
            $data['job_experience'] = $inputs['job_experience'];
            if(!isset($inputs['require_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Require skill'
                ];
            }
            $data['require_skill'] = json_encode($inputs['require_skill']);
            if(!isset($inputs['advance_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Advance skill'
                ];
            }
            $data['advance_skill'] = json_encode($inputs['advance_skill']);
            if(!isset($inputs['job_environmental'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job environmental'
                ];
            }
            $data['job_environmental'] = json_encode($inputs['job_environmental']);
        }
        else if($inputs['type'] === Post::TYPE_TIM_VIEC){
            if(!isset($inputs['work_position'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Work position'
                ];
            }
            $data['work_position'] = $inputs['work_position'];
            if(!isset($inputs['avg_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Average salary'
                ];
            }
            $data['avg_salary'] = $inputs['avg_salary'];
            if(!isset($inputs['min_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Min salary'
                ];
            }
            $data['min_salary'] = $inputs['min_salary'];
            if(!isset($inputs['max_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Max salary'
                ];
            }
            $data['max_salary'] = $inputs['max_salary'];
            if(!isset($inputs['job_type'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job type'
                ];
            }
            $data['job_type'] = $inputs['job_type'];
            if(!isset($inputs['job_contract'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job contract'
                ];
            }
            $data['job_contract'] = json_encode($inputs['job_contract']);
            if(!isset($inputs['job_time'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job time'
                ];
            }
            $data['job_time'] = $inputs['job_time'];
            if(!isset($inputs['job_experience'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job experience'
                ];
            }
            $data['job_experience'] = $inputs['job_experience'];
            if(!isset($inputs['require_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Require skill'
                ];
            }
            $data['require_skill'] = json_encode($inputs['require_skill']);
            if(!isset($inputs['advance_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Advance skill'
                ];
            }
            $data['advance_skill'] = json_encode($inputs['advance_skill']);
            if(!isset($inputs['job_environmental'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job environmental'
                ];
            }
            $data['job_environmental'] = json_encode($inputs['job_environmental']);
        }
        else if ($inputs['type'] === Post::TYPE_BUY){
            if(!isset($inputs['business_type'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Business type'
                ];
            }
            $data['business_type'] = $inputs['business_type'];
            if(!isset($inputs['facebook_name'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facebook name'
                ];
            }
            $data['facebook_name'] = $inputs['facebook_name'];
            if(!isset($inputs['facebook_url'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facebook url'
                ];
            }
            $data['facebook_url'] = $inputs['facebook_url'];
            if(!isset($inputs['instagram_name'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Instagram name'
                ];
            }
            $data['instagram_name'] = $inputs['instagram_name'];
            if(!isset($inputs['instagram_url'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Instagram url'
                ];
            }
            $data['instagram_url'] = $inputs['instagram_url'];
            if(!isset($inputs['facilities'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facilities'
                ];
            }
            $data['facilities'] = json_encode($inputs['facilities']);
            if(!isset($inputs['num_employees'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Number of employees'
                ];
            }
            $data['num_employees'] = $inputs['num_employees'];
            if(!isset($inputs['price'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Price'
                ];
            }
            $data['price'] = $inputs['price'];
            if(!isset($inputs['lease_agreement'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Lease agreement'
                ];
            }
            $data['lease_agreement'] = json_encode($inputs['lease_agreement']);
            if(!isset($inputs['avg_revenue'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Average revenue'
                ];
            }
            $data['avg_revenue'] = $inputs['avg_revenue'];
            if(!isset($inputs['support'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Support'
                ];
            }
            $data['support'] = $inputs['support'];
            if(!isset($inputs['additional_infor'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Additional information'
                ];
            }
            $data['additional_infor'] = json_encode($inputs['additional_infor']);
        }
        else if ($inputs['type'] === Post::TYPE_SELL) {
            if(!isset($inputs['business_type'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Business type'
                ];
            }
            $data['business_type'] = $inputs['business_type'];
            if(!isset($inputs['facebook_name'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facebook name'
                ];
            }
            $data['facebook_name'] = $inputs['facebook_name'];
            if(!isset($inputs['facebook_url'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facebook url'
                ];
            }
            $data['facebook_url'] = $inputs['facebook_url'];
            if(!isset($inputs['instagram_name'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Instagram name'
                ];
            }
            $data['instagram_name'] = $inputs['instagram_name'];
            if(!isset($inputs['instagram_url'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Instagram url'
                ];
            }
            $data['instagram_url'] = $inputs['instagram_url'];
            if(!isset($inputs['facilities'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facilities'
                ];
            }
            $data['facilities'] = json_encode($inputs['facilities']);
            if(!isset($inputs['num_employees'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Number of employees'
                ];
            }
            $data['num_employees'] = $inputs['num_employees'];
            if(!isset($inputs['price'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Price'
                ];
            }
            $data['price'] = $inputs['price'];
            if(!isset($inputs['lease_agreement'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Lease agreement'
                ];
            }
            $data['lease_agreement'] = json_encode($inputs['lease_agreement']);
            if(!isset($inputs['avg_revenue'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Average revenue'
                ];
            }
            $data['avg_revenue'] = $inputs['avg_revenue'];
            if(isset($inputs['support'])){
                $data['support'] = $inputs['support'];
            }
            if(!isset($inputs['additional_infor'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Additional information'
                ];
            }
            $data['additional_infor'] = json_encode($inputs['additional_infor']);
        }
        $data['slug'] = Str::slug($data['title']);
        $reference = isset($inputs['reference']) && !empty($inputs['reference']) ? $inputs['reference'] : null;
        if(!isset($id)) {
            $data['reference'] = $this->generateReference($reference);
        }
        $data['description'] = $inputs['description'];
        return [
            'is_failed' => false,
            'inputs' => $data
        ];
    }

    public function generateReference($reference) {
        if(!isset($reference)) {
            $now = Carbon::now();
            $pre_fix = Post::pre_fix.$now->format('ymd');
            $reference = $this->repo_base->getReferenceByPrefix($pre_fix, 'reference',5, true);
            return "{$reference}";
        }
        return $reference;
    }

    public function formatData($data)
    {
        $res = json_decode($data, true);
        if(isset($res['medias'])){
            $res['medias'] = json_decode($res['medias'], true);
        }
        if(isset($res['job_time'])){
            $res['job_time'] = json_decode($res['job_time'], true);
        }
        if(isset($res['require_skill'])){
            $res['require_skill'] = json_decode($res['require_skill'], true);
        }
        if(isset($res['advance_skill'])){
            $res['advance_skill'] = json_decode($res['advance_skill'], true);
        }
        if(isset($res['job_environmental'])){
            $res['job_environmental'] = json_decode($res['job_environmental'], true);
        }
        if(isset($res['lease_agreement'])){
            $res['lease_agreement'] = json_decode($res['lease_agreement'], true);
        }
        if(isset($res['facilities'])){
            $res['facilities'] = json_decode($res['facilities'], true);
        }
        if(isset($res['additional_infor'])){
            $res['additional_infor'] = json_decode($res['additional_infor'], true);
        }
        return $res;
    }

    public function generateColumn($inputs, $columns)
    {
        if(isset($inputs['category_id']) && $inputs['category_id'] !== 'all'){
            array_push($columns, $this->getTableName() . ".category_id = '{$inputs['category_id']}'");
        }
        if(isset($inputs['post_industry_id']) && $inputs['post_industry_id'] !== 'all'){
            array_push($columns, $this->getTableName() . ".post_industry_id = '{$inputs['post_industry_id']}'");
        }
        if(isset($inputs['sub_category_id']) && $inputs['sub_category_id'] !== 'all'){
            array_push($columns, $this->getTableName() . ".sub_category_id = '{$inputs['sub_category_id']}'");
        }
        if(isset($inputs['price']) && is_array($inputs['price']) && count($inputs['price']) >= 2 && $inputs['price'] !== 'all'){
            array_push($columns, $this->getTableName() . ".price BETWEEN {$inputs['price'][0]} AND {$inputs['price'][1]}");
        }
        if(isset($inputs['avg_revenue']) && is_array($inputs['avg_revenue']) && count($inputs['avg_revenue']) >= 2 && $inputs['avg_revenue'] !== 'all'){
            array_push($columns, $this->getTableName() . ".avg_revenue BETWEEN {$inputs['avg_revenue'][0]} AND {$inputs['avg_revenue'][1]}");
        }
        if(isset($inputs['min_salary']) && is_array($inputs['min_salary']) && count($inputs['min_salary']) >= 2 && $inputs['min_salary'] !== 'all'){
            array_push($columns, $this->getTableName() . ".min_salary BETWEEN {$inputs['min_salary'][0]} AND {$inputs['min_salary'][1]}");
        }
        if(isset($inputs['job_type']) && $inputs['job_type'] !== 'all'){
            array_push($columns, $this->getTableName() . ".job_type = '{$inputs['job_type']}'");
        }
        if(isset($inputs['type']) && $inputs['type'] !== 'all'){
            array_push($columns, $this->getTableName() . ".type = '{$inputs['type']}'");
        }
        if(isset($inputs['support']) && $inputs['support'] !== 'all'){
            array_push($columns, $this->getTableName() . ".support = '{$inputs['support']}'");
        }
        if(isset($inputs['num_employees']) && $inputs['num_employees'] !== 'all'){
            array_push($columns, $this->getTableName() . ".num_employees = '{$inputs['num_employees']}'");
        }
        return $columns;
    }

    public function exportExcel($inputs)
    {
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;
        $text = null;
        $columns = [];
        $columnsHas = [];
        $term = isset($inputs['term']) ? $inputs['term'] : [];
        $with = isset($inputs['with']) ? $inputs['with'] : $this->with;
        $page = isset($inputs['page']) ? $inputs['page'] : 1;
        $limit = isset($inputs['limit']) ? $inputs['limit'] : 99999;
        $orderBy = isset($inputs['order_by']) ? $inputs['order_by'] : 'created_at';
        $sort = isset($inputs['sort']) ? $inputs['sort'] : 'desc';
        $joins = $this->getJoinTable();

        $orderBy = $this->generateOrder($orderBy);
        $select = $this->generateSelect($inputs, $this->getTableName());
        // status
        $columns = $this->generateColumn($inputs['filter'], $columns);
        // generate conditions from term
        $query = $this->generateQuery($term, $columns);
        $columns = $query['columns'];
        $text = $query['search'];

        $datas = $this->repo_base->searchText($text, $columns, $columnsHas, $joins, $page, $limit, $orderBy, $sort, $with, $select);

        if (count($datas) > 0) {
            return $this->service_export_excel->exportExcel(
                $this->formatExcelSelectData($datas),
                config('excel_enums.post.excel_name'). '_' . time() . config('excel_enums.excel_ext'),
                $this->generateHeaderExcel(),
                config('excel_enums.post.subject_name'),
                ['A1:V1']
            );
        }
        return
            [
                'success' => false,
                'error' =>  sprintf(config('error_code')['080'], 'Nhân viên'),
                'code' => '080'
            ];
    }

    public function formatExcelSelectData($datas)
    {
        $res = [];
        array_push($res, $this->generateExcelColumn());
        foreach ($datas as $key => $data) {
            array_push($res, $this->formatExcelData($data, $key));
        }
        return $res;
    }

    public function generateExcelColumn()
    {
        $open_style = '<b><style border="#000000" bgcolor="#BCF79C"><center>';
        $close_stype = '</center></style></b>';
        return [
            'id' => $open_style . 'STT' . $close_stype,
            'reference' => $open_style .  'Mã bài đăng' . $close_stype,
            'category_name' => $open_style . 'Tên menu' . $close_stype,
            'sub_category_name' => $open_style . 'Tên sub menu' . $close_stype,
            'post_industry_name' => $open_style . 'Loại industry' . $close_stype,
            'type' => $open_style . 'Loại bài đăng' . $close_stype,
            'status' => $open_style . 'Trạng thái' . $close_stype,
            'title' => $open_style . 'Tiêu đề' . $close_stype,
            'phone_number' => $open_style . 'Số điện thoại' . $close_stype,
            'email' => $open_style . 'Địa chỉ email' . $close_stype,
            'website' => $open_style . 'Link website' . $close_stype,
            'store_name' => $open_style . 'Tên shop' . $close_stype,
            'store_address' => $open_style . 'Địa chỉ shop' . $close_stype,
            'store_area' => $open_style . 'Khu vực' . $close_stype,
            'avg_salary' => $open_style . 'Lương trung bình' . $close_stype,
            'min_salary' => $open_style . 'Lương thấp nhất' . $close_stype,
            'max_salary' => $open_style . 'Lương cao nhất' . $close_stype,
            'type_salary' => $open_style . 'Loại nhận lương' . $close_stype,
            'price' => $open_style . 'Giá' . $close_stype,
            'avg_revenue' => $open_style . 'Doanh thu trung bình' . $close_stype,
            'created_at' => $open_style . 'Ngày tạo' . $close_stype,
            'updated_at' => $open_style . 'Ngày cập nhật' . $close_stype,
        ];
    }

    public function generateHeaderExcel()
    {
        $open_style = '<center>';
        $close_stype = '</center>';
        return [
            'id' => $open_style . 'THÔNG TIN BÀI VIẾT' . $close_stype,
            'reference' => '',
            'category_name' => '',
            'sub_category_name' => '',
            'post_industry_name' => '',
            'type' => '',
            'status' => '',
            'title' => '',
            'phone_number' => '',
            'email' => '',
            'store_name' => '',
            'store_address' => '',
            'store_area' => '',
            'avg_salary' => '',
            'min_salary' => '',
            'max_salary' => '',
            'type_salary' => '',
            'price' => '',
            'avg_revenue' => '',
            'created_at' => '',
            'updated_at' => '',
        ];
    }

    public function formatExcelData($data, $key)
    {
        $res = [
            'id' => $key + 1,
            'reference' => $data->reference,
            'category_name' => isset($data->category_name) ? $data->category_name : '',
            'sub_category_name' => isset($data->sub_category_name) ? $data->sub_category_name : '',
            'post_industry_name' => isset($data->post_industry_name) ? $data->post_industry_name : '',
            'type' => isset($data->type) ? $data->type : '',
            'status' => isset($data->status) ? $data->status : '',
            'title' => isset($data->title) ? $data->title : '',
            'phone_number' => isset($data->phone_number) ? $data->phone_number : '',
            'email' => isset($data->email) ? $data->email : '',
            'website' => isset($data->website) ? $data->website : '',
            'store_name' => isset($data->store_name) ? $data->store_name : '',
            'store_address' => isset($data->store_address) ? $data->store_address : '',
            'store_area' => $data->store_area,
            'avg_salary' => isset($data->avg_salary) ? $data->avg_salary : 0,
            'min_salary' => isset($data->min_salary) ? $data->min_salary : 0,
            'max_salary' => isset($data->max_salary) ? $data->max_salary : 0,
            'type_salary' => isset($data->type_salary) ? $data->type_salary : 1,
            'price' => isset($data->price) ? $data->price : 0,
            'avg_revenue' => isset($data->avg_revenue) ? $data->avg_revenue : 0,
            'created_at' => isset($data->created_at) ? Carbon::parse($data->created_at)->format('d/m/Y H:i:s') : '',
            'updated_at' => isset($data->updated_at) ? Carbon::parse($data->updated_at)->format('d/m/Y H:i:s') : '',
        ];
        return $res;
    }

    public function getQueryDateField()
    {
        return [
            $this->getTableName() . '.created_at',
            $this->getTableName() . '.updated_at',
        ];
    }

    public function getQueryField()
    {
        return [
            $this->getTableName() . '.reference',
            $this->getTableName() . '.category_name',
            $this->getTableName() . '.sub_category_name',
            $this->getTableName() . '.post_industry_name',
            $this->getTableName() . '.phone_number',
            $this->getTableName() . '.description',
            $this->getTableName() . '.title',
            $this->getTableName() . '.store_name',
            $this->getTableName() . '.store_address',
            $this->getTableName() . '.store_area',

            $this->getTableName() . '.lease_agreement',
            $this->getTableName() . '.avg_salary',
            $this->getTableName() . '.min_salary',
            $this->getTableName() . '.max_salary',
            $this->getTableName() . '.type_salary',

            $this->getTableName() . '.job_type',
            $this->getTableName() . '.job_contract',
            $this->getTableName() . '.avg_revenue',
        ];
    }
}
