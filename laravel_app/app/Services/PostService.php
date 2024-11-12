<?php

namespace App\Services;

use App\Constants\QueueMap;
use App\Jobs\CreateKeywordJob;
use App\Jobs\DeleteKeywordJob;
use App\Jobs\SyncFilterElasticsearch;
use App\Models\Post;
use App\Repositories\Interfaces\PostIndustryRepositoryInterface;
use App\Repositories\Interfaces\PostJobRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\PostSaleRepositoryInterface;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Repositories\Interfaces\UserRecentSearchRepositoryInterface;
use App\Repositories\Interfaces\UserSearchRepositoryInterface;
use App\Services\Base\BaseService;
use App\Services\Client\FilterElasticsearchService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostService extends BaseService
{
    protected $repo_base;
    protected SubCategoryRepositoryInterface $repo_sub_category;
    protected PostIndustryRepositoryInterface $repo_post_industry;
    protected UserRecentSearchRepositoryInterface $repo_user_recent_search;
    protected UserSearchRepositoryInterface $repo_user_search;
    protected PostJobRepositoryInterface $repo_post_job;
    protected PostSaleRepositoryInterface $repo_post_sale;
    protected $with;

    public function __construct(
        PostRepositoryInterface $repo_base,
        SubCategoryRepositoryInterface $repo_sub_category,
        PostIndustryRepositoryInterface $repo_post_industry,
        UserRecentSearchRepositoryInterface $repo_user_recent_search,
        UserSearchRepositoryInterface $repo_user_search,
        PostJobRepositoryInterface $repo_post_job,
        PostSaleRepositoryInterface $repo_post_sale
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_sub_category = $repo_sub_category;
        $this->repo_post_industry = $repo_post_industry;
        $this->repo_user_recent_search = $repo_user_recent_search;
        $this->repo_user_search = $repo_user_search;
        $this->repo_post_job = $repo_post_job;
        $this->repo_post_sale = $repo_post_sale;
        $this->with = ['postSale', 'postJob'];
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
        $data_sale = null;
        $data_job = null;
        if(in_array($input_data['type'], [Post::TYPE_TIM_VIEC, Post::TYPE_TUYEN_DUNG])){
            if(isset($input_data['job'])){
                $data_job = $input_data['job'];
                unset($input_data['job']);
            }
        }
        else if(in_array($input_data['type'], [Post::TYPE_SELL, Post::TYPE_BUY])){
            if(isset($input_data['sale'])){
                $data_sale = $input_data['sale'];
                unset($input_data['sale']);
            }
        }

        $input_data['start_date'] = Carbon::now()->toDateTime();
        $input_data['end_date'] = Carbon::now()->toDateTime();
        $data = $this->repo_base->create($input_data);

        if(isset($data_job)){
            $data_job['post_id'] = $data->id;
            $data_job['id'] = Str::orderedUuid();
            $this->repo_post_job->create($data_job);
        }else if(isset($data_sale)){
            $data_sale['post_id'] = $data->id;
            $data_sale['id'] = Str::orderedUuid();
            $this->repo_post_sale->create($data_sale);
        }

        $data = $this->repo_base->findById($data->id, $this->with);

        $text = "{$data->title}. {$data->description}";
        dispatch((new CreateKeywordJob($text, $data->id))->onQueue(QueueMap::QUEUE_GENERATE_KEYWORD));
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

        $data_sale = null;
        $data_job = null;
        if(in_array($input_data['type'], [Post::TYPE_TIM_VIEC, Post::TYPE_TUYEN_DUNG])){
            if(isset($input_data['job'])){
                $data_job = $input_data['job'];
                unset($input_data['job']);
            }
        }
        else if(in_array($input_data['type'], [Post::TYPE_SELL, Post::TYPE_BUY])){
            if(isset($input_data['sale'])){
                $data_sale = $input_data['sale'];
                unset($input_data['sale']);
            }
        }

        $this->repo_base->update($id, $input_data);

        if(isset($data_job)){
            $data_job['post_id'] = $data->id;
            $data_job['id'] = Str::orderedUuid();
            $post_job = $this->repo_post_job->findOneBy(['post_id' => $data->id]);
            $this->repo_post_job->update($post_job->id, $data_job);
        }else if(isset($data_sale)){
            $data_sale['post_id'] = $data->id;
            $data_sale['id'] = Str::orderedUuid();
            $post_sale = $this->repo_post_sale->findOneBy(['post_id' => $data->id]);
            $this->repo_post_sale->update($post_sale->id, $data_sale);
        }

        $data = $this->repo_base->findById($data->id, $this->with);
        $text = "{$data->title}. {$data->description}";
        dispatch((new CreateKeywordJob($text, $data->id))->onQueue(QueueMap::QUEUE_GENERATE_KEYWORD));
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

        dispatch((new DeleteKeywordJob($data->id))->onQueue(QueueMap::QUEUE_GENERATE_KEYWORD));
        $data->delete();

        return [
            'code' => '200',
            'message' => 'Deleted successfully'
        ];
    }

    public function searchElastic($inputs)
    {
        $user = Auth::guard('users')->user();
        $inputs["limit"] = $inputs["limit"] ?? 1000;
        $inputs["search"] = $inputs["search"] ?? "";
        $isSelect = $inputs["is_select"] ?? 1;

        $search = $this->setSearchElastic($inputs);

        if ($isSelect === 2 || $isSelect === 3) {
            $dataRaw = $search->paginateRaw($inputs["limit"]);
            $datasArray = [];
            foreach ($dataRaw->items() as $i) {
                $datasArray = $datasArray + $i["hits"]["hits"];
            }

            $datasLookup = array_map(fn($item) => array_merge(
                $item["_source"],
                !empty ($item['sort']) ? ['sort' => $item['sort'][0]] : []
            ), $datasArray);

            if ($isSelect === 2) {
                $datas = $dataRaw->setCollection(collect($datasLookup));
            } else {
                $dataPaginate = $search->paginate($inputs["limit"]);
                $dataArrayMer = [];
                foreach ($dataPaginate->items() as $index => $paginateItem) {
                    $dataArrayMer[] = array_merge($paginateItem->getAttributes(), $datasLookup[$index]);
                }
                $datas = $dataPaginate->setCollection(collect($dataArrayMer));
            }
        } else {
            $dataPaginate = $search->paginate($inputs["limit"]);
            $datas = $dataPaginate;
        }

        if(isset($inputs['is_save_search']) && $inputs['is_save_search'] === true){
            $this->saveSavedSearchUser($inputs, $user);
        }
        $this->saveRecentSearchUser($inputs, $user);

        return [
            "code" => "200",
            "data" => $datas
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

        if(!isset($inputs['post_industry_id'])) {
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'sub category'
            ];
        }
        $data['post_industry_id'] = $inputs['post_industry_id'];

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

        $post_industry = $this->repo_post_industry->findById($data['post_industry_id']);

        if(isset($post_industry)){
            if($post_industry->sub_category_id !== $data['sub_category_id']){
                return [
                    'is_failed' => true,
                    'code' => '008',
                    'message' => 'Post industry'
                ];
            }
            $data['post_industry_id'] = $post_industry->id;
            $data['post_industry_name'] = $post_industry->name;
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
            $data['job']['work_position'] = $inputs['work_position'];
            if(!isset($inputs['avg_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Average salary'
                ];
            }
            $data['job']['avg_salary'] = $inputs['avg_salary'];
            if(!isset($inputs['min_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Min salary'
                ];
            }
            $data['job']['min_salary'] = $inputs['min_salary'];
            if(!isset($inputs['max_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Max salary'
                ];
            }
            $data['job']['max_salary'] = $inputs['max_salary'];
            if(!isset($inputs['job_type'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job type'
                ];
            }
            $data['job']['job_type'] = $inputs['job_type'];
            if(!isset($inputs['job_contract'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job contract'
                ];
            }
            $data['job']['job_contract'] = json_encode($inputs['job_contract'], JSON_UNESCAPED_UNICODE);
            if(!isset($inputs['job_time'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job time'
                ];
            }
            $data['job']['job_time'] = json_encode($inputs['job_time']);
            if(!isset($inputs['job_experience'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job experience'
                ];
            }
            $data['job']['job_experience'] = $inputs['job_experience'];
            if(!isset($inputs['require_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Require skill'
                ];
            }
            $data['job']['require_skill'] = json_encode($inputs['require_skill']);
            if(!isset($inputs['advance_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Advance skill'
                ];
            }
            $data['job']['advance_skill'] = json_encode($inputs['advance_skill']);
            if(!isset($inputs['job_environmental'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job environmental'
                ];
            }
            $data['job']['job_environmental'] = json_encode($inputs['job_environmental']);
        }
        else if($inputs['type'] === Post::TYPE_TIM_VIEC){
            if(!isset($inputs['work_position'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Work position'
                ];
            }
            $data['job']['work_position'] = $inputs['work_position'];
            if(!isset($inputs['avg_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Average salary'
                ];
            }
            $data['job']['avg_salary'] = $inputs['avg_salary'];
            if(!isset($inputs['min_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Min salary'
                ];
            }
            $data['job']['min_salary'] = $inputs['min_salary'];
            if(!isset($inputs['max_salary'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Max salary'
                ];
            }
            $data['job']['max_salary'] = $inputs['max_salary'];
            if(!isset($inputs['job_type'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job type'
                ];
            }
            $data['job']['job_type'] = $inputs['job_type'];
            if(!isset($inputs['job_contract'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job contract'
                ];
            }
            $data['job']['job_contract'] = json_encode($inputs['job_contract']);
            if(!isset($inputs['job_time'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job time'
                ];
            }
            $data['job']['job_time'] = $inputs['job_time'];
            if(!isset($inputs['job_experience'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job experience'
                ];
            }
            $data['job']['job_experience'] = $inputs['job_experience'];
            if(!isset($inputs['require_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Require skill'
                ];
            }
            $data['job']['require_skill'] = json_encode($inputs['require_skill']);
            if(!isset($inputs['advance_skill'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Advance skill'
                ];
            }
            $data['job']['advance_skill'] = json_encode($inputs['advance_skill']);
            if(!isset($inputs['job_environmental'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Job environmental'
                ];
            }
            $data['job']['job_environmental'] = json_encode($inputs['job_environmental']);
        }
        else if ($inputs['type'] === Post::TYPE_BUY){
            if(!isset($inputs['business_type'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Business type'
                ];
            }
            $data['sale']['business_type'] = $inputs['business_type'];
            if(!isset($inputs['facebook_name'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facebook name'
                ];
            }
            $data['sale']['facebook_name'] = $inputs['facebook_name'];
            if(!isset($inputs['facebook_url'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facebook url'
                ];
            }
            $data['sale']['facebook_url'] = $inputs['facebook_url'];
            if(!isset($inputs['instagram_name'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Instagram name'
                ];
            }
            $data['sale']['instagram_name'] = $inputs['instagram_name'];
            if(!isset($inputs['instagram_url'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Instagram url'
                ];
            }
            $data['sale']['instagram_url'] = $inputs['instagram_url'];
            if(!isset($inputs['facilities'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facilities'
                ];
            }
            $data['sale']['facilities'] = json_encode($inputs['facilities']);
            if(!isset($inputs['num_employees'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Number of employees'
                ];
            }
            $data['sale']['num_employees'] = $inputs['num_employees'];
            if(!isset($inputs['price'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Price'
                ];
            }
            $data['sale']['price'] = $inputs['price'];
            if(!isset($inputs['lease_agreement'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Lease agreement'
                ];
            }
            $data['sale']['lease_agreement'] = $inputs['lease_agreement'];
            if(!isset($inputs['avg_revenue'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Average revenue'
                ];
            }
            $data['sale']['avg_revenue'] = $inputs['avg_revenue'];
            if(!isset($inputs['support'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Support'
                ];
            }
            $data['sale']['support'] = $inputs['support'];
//            if(!isset($inputs['additional_infor'])){
//                return [
//                    'is_failed' => true,
//                    'code' => '003',
//                    'message' => 'Additional information'
//                ];
//            }
//            $data['sale]['additional_infor'] = json_encode($inputs['additional_infor']);

            if(isset($inputs['nearby_areas'])){
                $data['sale']['nearby_areas'] = json_encode($inputs['nearby_areas']);
            }
        }
        else if ($inputs['type'] === Post::TYPE_SELL) {
            if(!isset($inputs['business_type'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Business type'
                ];
            }
            $data['sale']['business_type'] = $inputs['business_type'];
            if(!isset($inputs['facebook_name'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facebook name'
                ];
            }
            $data['sale']['facebook_name'] = $inputs['facebook_name'];
            if(!isset($inputs['facebook_url'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facebook url'
                ];
            }
            $data['sale']['facebook_url'] = $inputs['facebook_url'];
            if(!isset($inputs['instagram_name'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Instagram name'
                ];
            }
            $data['sale']['instagram_name'] = $inputs['instagram_name'];
            if(!isset($inputs['instagram_url'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Instagram url'
                ];
            }
            $data['sale']['instagram_url'] = $inputs['instagram_url'];
            if(!isset($inputs['facilities'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Facilities'
                ];
            }
            $data['sale']['facilities'] = json_encode($inputs['facilities']);
            if(!isset($inputs['num_employees'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Number of employees'
                ];
            }
            $data['sale']['num_employees'] = $inputs['num_employees'];
            if(!isset($inputs['price'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Price'
                ];
            }
            $data['sale']['price'] = $inputs['price'];
            if(!isset($inputs['lease_agreement'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Lease agreement'
                ];
            }
            $data['sale']['lease_agreement'] = json_encode($inputs['lease_agreement']);
            if(!isset($inputs['avg_revenue'])){
                return [
                    'is_failed' => true,
                    'code' => '003',
                    'message' => 'Average revenue'
                ];
            }
            $data['sale']['avg_revenue'] = $inputs['avg_revenue'];
            if(isset($inputs['support'])){
                $data['sale']['support'] = $inputs['support'];
            }
//            if(!isset($inputs['additional_infor'])){
//                return [
//                    'is_failed' => true,
//                    'code' => '003',
//                    'message' => 'Additional information'
//                ];
//            }
//            $data['sale]['additional_infor'] = json_encode($inputs['additional_infor']);

            if(isset($inputs['nearby_areas'])){
                $data['sale']['nearby_areas'] = json_encode($inputs['nearby_areas']);
            }
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
        if(isset($res['post_job'])){
            if(isset($res['post_job']['job_time'])){
                $res['post_job']['job_time'] = json_decode($res['post_job']['job_time'], true);
            }
            if(isset($res['post_job']['require_skill'])){
                $res['post_job']['require_skill'] = json_decode($res['post_job']['require_skill'], true);
            }
            if(isset($res['post_job']['advance_skill'])){
                $res['post_job']['advance_skill'] = json_decode($res['post_job']['advance_skill'], true);
            }
            if(isset($res['post_job']['job_environmental'])){
                $res['post_job']['job_environmental'] = json_decode($res['post_job']['job_environmental'], true);
            }
        }

        if(isset($res['additional_infor'])){
            $res['additional_infor'] = json_decode($res['additional_infor'], true);
        }
        if(isset($res['nearby_areas'])){
            $res['nearby_areas'] = json_decode($res['nearby_areas'], true);
        }
        if(isset($res['post_sale'])){
            if(isset($res['post_sale']['facilities'])){
                $res['post_sale']['facilities'] = json_decode($res['post_sale']['facilities'], true);
            }
            if(isset($res['post_sale']['lease_agreement'])){
                $res['post_sale']['lease_agreement'] = json_decode($res['post_sale']['lease_agreement'], true);
            }
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

    protected function saveSavedSearchUser($inputs, $user){
        if(isset($inputs['filter']['field']) && $inputs['filter']['field'] === 'category_id'){
            $data= [];
            $data['user_id'] = $user->id;
            if(isset($inputs['multi_match']) && isset($inputs['multi_match']['value'])){
                $data['keyword'] = $inputs['multi_match']['value'];
            }
            $location = null;
            if(isset($inputs['geo_distance']) && isset($inputs['geo_distance']['location'])){
                if(isset($inputs['geo_distance']['location']['lat'])){
                    $location['lat'] = $inputs['geo_distance']['location']['lat'];
                }
                if(isset($inputs['geo_distance']['location']['lon'])){
                    $location['lng'] = $inputs['geo_distance']['location']['lon'];
                }
            }
            if(isset($location)){
                $data['location'] = json_encode($location, JSON_UNESCAPED_UNICODE);
            }
            $data['data_search'] = json_encode($inputs, JSON_UNESCAPED_UNICODE);

            if(isset($inputs['filter'])){
                $data['category_id'] = $inputs['filter']['value'];
            }
            $filter = $this->repo_user_search->findOneBy([
                'category_id' => $data['category_id'],
                'user_id' => $user->id
            ]);

            if(isset($filter)){
                $filter = $this->repo_user_search->update($filter->id,$data);
            }else{
                $filter = $this->repo_user_search->create($data);
            }

            // Khởi tạo mảng ánh xạ các field với các key trong $data
            $termFields = [
                'sub_category_id' => 'sub_category_id',
                'post_industry_id' => 'post_industry_id',
            ];

            $rangeFields = [
                'num_employees' => 'num_employees',
                'avg_revenue' => 'avg_revenue',
                'lease_agreement.lease_remaining' => 'lease_remaining',
                'facilities.num_chairs' => 'num_chairs',
                'facilities.num_tables' => 'num_tables',
            ];

            // Xử lý các terms
            foreach ($inputs['terms'] as $term) {
                if (isset($term['field'], $term['values']) && isset($termFields[$term['field']])) {
                    $data[$termFields[$term['field']]] = $term['values'];
                }
            }

            // Xử lý các ranges
            foreach ($inputs['ranges'] as $range) {
                if (isset($range['field'], $range['option'])
                    && isset($range['option']['lte'], $range['option']['gte'])
                    && isset($rangeFields[$range['field']])
                ) {
                    $data[$rangeFields[$range['field']]] = $range['option'];
                }
            }
            $data['id'] = $filter->id;
            $data['location'] = [
                'lat' => $location['lat'],
                'lon' => $location['lng']
            ];
            unset($data['data_search']);
//            $this->syncFilterElasticsearch($data);
            dispatch((new SyncFilterElasticsearch($data))->onQueue(QueueMap::QUEUE_SYNC_FILTER_ELASTICSEARCH));
        }
    }

    protected function saveRecentSearchUser($inputs, $user){
        $data = [];
        $data['user_id'] = $user->id;
        if(isset($inputs['multi_match']) && isset($inputs['multi_match']['value'])){
            $data['keyword'] = $inputs['multi_match']['value'];
        }
        $location = null;
        if(isset($inputs['geo_distance']) && isset($inputs['geo_distance']['location'])){
            if(isset($inputs['geo_distance']['location']['lat'])){
                $location['lat'] = $inputs['geo_distance']['location']['lat'];
            }
            if(isset($inputs['geo_distance']['location']['lon'])){
                $location['lng'] = $inputs['geo_distance']['location']['lon'];
            }
        }
        if(isset($location)){
            $data['location'] = json_encode($location, JSON_UNESCAPED_UNICODE);
        }
        $data['data_search'] = json_encode($inputs, JSON_UNESCAPED_UNICODE);

        $this->repo_user_recent_search->create($data);
    }

    public function syncFilterElasticsearch($inputs){
        $elasticsearchService = app(FilterElasticsearchService::class);
        $elasticsearchService->addToElasticsearch($inputs);
    }
}
