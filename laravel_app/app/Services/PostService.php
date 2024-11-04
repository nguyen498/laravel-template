<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\Interfaces\PostIndustryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Services\Base\BaseService;
use App\Utils\StringHelpers;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PostService extends BaseService
{
    protected $repo_base;
    protected $repo_sub_category;
    protected $repo_post_industry;
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
        $validate = $this->checkInputs($inputs, null);
        if ($validate['is_failed']) {
            return $validate;
        }
        $input_data = $validate['inputs'];

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

        $post_industry = $this->repo_post_industry->findWhereBy([
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
        $data = $this->repo_base->findById($id);
        if (!isset($data)) {
            return ['code' => '004', 'message' => $this->getModelName()];
        }
        $validate = $this->checkInputs($inputs, $id);
        if ($validate['is_failed']) {
            return $validate;
        }
        $input_data = $validate['inputs'];

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

        $post_industry = $this->repo_post_industry->findWhereBy([
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

    public function checkInputs($inputs, $id)
    {
        $data = [];

        if(!isset($inputs['store_name'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'Store name'
            ];
        }
        if(!isset($inputs['store_address'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'Store address'
            ];
        }
        if(!isset($inputs['lng'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'Store address'
            ];
        }
        if(!isset($inputs['lat'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'Store address'
            ];
        }
        if(!isset($inputs['title'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'Title'
            ];
        }
        if(!isset($inputs['medias'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'Medias'
            ];
        }
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
                'message' => 'Store name'
            ];
        }
        $data['sub_category_id'] = $inputs['sub_category_id'];

        if(!isset($inputs['type'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'Type'
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
            $data['job_contract'] = $inputs['job_contract'];
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
            $data['require_skill'] = $inputs['require_skill'];
            if(!isset($inputs['advance_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Advance skill'
                ];
            }
            $data['advance_skill'] = $inputs['advance_skill'];
            if(!isset($inputs['job_environmental'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job environmental'
                ];
            }
            $data['job_environmental'] = $inputs['job_environmental'];
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
            $data['job_contract'] = $inputs['job_contract'];
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
            $data['require_skill'] = $inputs['require_skill'];
            if(!isset($inputs['advance_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Advance skill'
                ];
            }
            $data['advance_skill'] = $inputs['advance_skill'];
            if(!isset($inputs['job_environmental'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job environmental'
                ];
            }
            $data['job_environmental'] = $inputs['job_environmental'];
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
            $data['facilities'] = $inputs['facilities'];
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
            $data['lease_agreement'] = $inputs['lease_agreement'];
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
            $data['additional_infor'] = $inputs['additional_infor'];
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
            $data['facilities'] = $inputs['facilities'];
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
            $data['lease_agreement'] = $inputs['lease_agreement'];
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
            $data['additional_infor'] = $inputs['additional_infor'];
        }
        $inputs['slug'] = Str::slug($inputs['title']);
        $reference = isset($inputs['reference']) && !empty($inputs['reference']) ? $inputs['reference'] : null;
        if(!isset($id)) {
            $inputs['reference'] = $this->generateReference($reference);
        }
        return [
            'is_failed' => false,
            'inputs' => $data
        ];
    }

    public function generateReference($reference) {
        if(!isset($reference)) {
            $now = Carbon::now();
            $reference = $this->repo_base->getReferenceByPrefix($now->format('ymd'), 'reference',5, true);
            $pre_fix = Post::pre_fix;
            return "{$pre_fix}{$reference}";
        }
        return $reference;
    }
}
