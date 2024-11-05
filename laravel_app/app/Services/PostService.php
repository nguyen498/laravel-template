<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\Interfaces\PostIndustryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Services\Base\BaseService;
use App\Utils\StringHelpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostService extends BaseService
{
    protected $repo_base;
    protected SubCategoryRepositoryInterface $repo_sub_category;
    protected PostIndustryRepositoryInterface $repo_post_industry;
    protected $with;

    public function __construct(
        PostRepositoryInterface $repo_base,
        SubCategoryRepositoryInterface $repo_sub_category,
        PostIndustryRepositoryInterface $repo_post_industry
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_sub_category = $repo_sub_category;
        $this->repo_post_industry = $repo_post_industry;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Post';
    }

    public function getTableName()
    {
        return (new Post())->getTable();
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

        $sub_category = $this->repo_sub_category->findById($input_data['sub_category_id'], ['category']);
        if(!isset($sub_category)){
            return [
                'is_failed' => true,
                'code' => '004',
                'message' => 'Sub category'
            ];
        }

        $input_data['sub_category_name'] = $sub_category->name;
        $input_data['category_id'] = $sub_category->category->id;
        $input_data['category_name'] = $sub_category->category->name;

        $post_industry = $this->repo_post_industry->findOneBy([
           'sub_category_id' => $input_data['sub_category_id']
        ]);
        if(isset($post_industry)){
            $input_data['post_industry_id'] = $post_industry->id;
            $input_data['post_industry_name'] = $post_industry->title;
        }

        $data = $this->repo_base->create($input_data);
        $data = $this->repo_base->findById($data->id, $this->with);
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

        $sub_category = $this->repo_sub_category->findById($input_data['sub_category_id'], ['category']);
        if(!isset($sub_category)){
            return [
                'is_failed' => true,
                'code' => '004',
                'message' => 'Sub category'
            ];
        }

        $input_data['sub_category_name'] = $sub_category->name;
        $input_data['category_id'] = $sub_category->category->id;
        $input_data['category_name'] = $sub_category->category->name;

        $post_industry = $this->repo_post_industry->findOneBy([
            'sub_category_id' => $input_data['sub_category_id']
        ]);
        if(isset($post_industry)){
            $input_data['post_industry_id'] = $post_industry->id;
            $input_data['post_industry_name'] = $post_industry->title;
        }


        $this->repo_base->update($id, $input_data);
        $data = $this->repo_base->findById($data->id, $this->with);
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
        if(!isset($inputs['title'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'title'
            ];
        }
        $data['title'] = $inputs['title'];

        if(!isset($inputs['phone'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'phone'
            ];
        }
        $data['phone'] = $inputs['phone'];

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
//        if(!isset($inputs['category_id'])){
//            return [
//                'is_failed' => true,
//                'code' => '003',
//                'message' => 'Store name'
//            ];
//        }
        if(!isset($inputs['sub_category_id'])) {
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'sub category'
            ];
        }
        $data['sub_category_id'] = $inputs['sub_category_id'];

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
}
