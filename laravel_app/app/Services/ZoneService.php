<?php

namespace App\Services;

use App\Models\Zone;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Repositories\Interfaces\ImpressionRepositoryInterface;
use App\Repositories\Interfaces\ZoneRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Support\Facades\Auth;

class ZoneService extends  BaseService
{
    protected $repo_base;
    protected $repo_impression;
    protected $repo_banner;
    protected $service_post;
    protected $with;

    public function __construct(
        ZoneRepositoryInterface $repo_base,
        ImpressionRepositoryInterface $repo_impression,
        BannerRepositoryInterface $repo_banner,
        PostService $service_post,
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_impression = $repo_impression;
        $this->repo_banner = $repo_banner;

        $this->service_post = $service_post;

        $this->with = [];
    }

    public function getModelName()
    {
        return 'Zone';
    }

    public function getTableName()
    {
        return (new Zone())->getTable();
    }

    public function getBanners($inputs) {
        $user = Auth::guard('users')->user();
        if(!isset($inputs['type'])) { return [ 'code' => '003', 'message' => 'Loại zone' ]; }
        $zone = $this->repo_base->findOneBy([
            'type' => $inputs['type']
        ]);
        $exist_banners = $this->repo_banner->findByVisitorId($user->id, config('advertiser_config.display_rule'));
        if(!isset($zone)) { return [ 'code' => '004', 'message' => 'Zone' ]; }
        $exist_banner_ids = [];
        foreach($exist_banners as $bann) {
            array_push($exist_banner_ids, $bann->id);
        }
        $banners = $this->repo_base->getBanners($zone, $exist_banner_ids);

        return [
            'code' => '200',
            'data' => $banners
        ];
    }

    public function getBanners_v2($inputs) {
        $user = Auth::guard('users')->user();
        if(!isset($inputs['advert_type']) && count($inputs['advert_type']) > 0){
            return [
                'code' => '003',
                'message' => 'advert type'
            ];
        }

        $zone = $this->repo_base->findOneBy([
            'type' => $inputs['advert_type'][0]
        ]);
        $exist_banners = $this->repo_banner->findByVisitorId($user->id, config('advertiser_config.display_rule'));
        if(!isset($zone)) { return [ 'code' => '004', 'message' => 'Zone' ]; }
        $exist_banner_ids = [];
        foreach($exist_banners as $bann) {
            array_push($exist_banner_ids, $bann->post_id);
        }

        $data_to_send = $inputs;
        $data_to_send['limit'] = 10000;
        if(count($exist_banner_ids) > 0){
            $data_to_send = array_merge($data_to_send, [
                "ids" => $exist_banner_ids
            ]);
        }
        if(!isset($inputs['terms'])){
            $data_to_send = array_merge($data_to_send, [
                'terms' => [
                    [
                        "field" => "advert_type",
                        "values" => $inputs['advert_type']
                    ]
                ]
            ]);
        }else{
            $data_to_send['terms'][] = [
                "field" => "advert_type",
                "values" => $data_to_send['advert_type']
            ];
        }
        $res_search = $this->service_post->searchElastic($data_to_send);
        if($res_search['code'] !== '200'){
            return $res_search;
        }
        $data_posts = $res_search['data']['data'];

        return [
            'code' => '200',
            'data' => $data_posts
        ];
    }
}
