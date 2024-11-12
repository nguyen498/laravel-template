<?php

namespace App\Services;

use App\Models\Advertiser;
use App\Models\AdvertisingRequest;
use App\Models\Banner;
use App\Models\Campaign;
use App\Repositories\Interfaces\AdvertiserRepositoryInterface;
use App\Repositories\Interfaces\AdvertisingRequestRepositoryInterface;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Repositories\Interfaces\CampaignRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\ZoneRepositoryInterface;
use App\Services\Base\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdvertisingRequestService extends BaseService
{
    protected $repo_base;
    protected $repo_post;
    protected $repo_advertiser;
    protected $repo_campaign;
    protected $repo_banner;
    protected $repo_zone;
    protected $with;

    public function __construct(
        AdvertisingRequestRepositoryInterface $repo_base,
        PostRepositoryInterface $repo_post,
        AdvertiserRepositoryInterface $repo_advertiser,
        CampaignRepositoryInterface $repo_campaign,
        BannerRepositoryInterface $repo_banner,
        ZoneRepositoryInterface $repo_zone
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_post = $repo_post;
        $this->repo_advertiser = $repo_advertiser;
        $this->repo_campaign = $repo_campaign;
        $this->repo_banner = $repo_banner;
        $this->repo_zone = $repo_zone;
        $this->with = ['user', 'post'];
    }

    public function getModelName()
    {
        return 'Advertising Request';
    }

    public function getTableName()
    {
        return (new AdvertisingRequest())->getTable();
    }

    public function storeApp($inputs) {
        $user = Auth::guard('users')->user();
        // double check post exist with status
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;

        $validate = $this->checkInputs($inputs, null);
        if ($validate['is_failed']) {
            return $validate;
        }
        $input_data = $validate['inputs'];
        // check exist
        if($this->repo_base->existByWhere([
            'post_id' => $input_data['post_id'],
            'user_id' => $user->id,
            'status' => AdvertisingRequest::STATUS_NEW
        ], null)) {
            return [ 'code' => '005', 'message' => 'Yêu cầu quảng cáo' ];
        }
        $input_data['user_id'] = $user->id;

        $data = $this->repo_base->create($input_data);
        $data = $this->repo_base->findById($data->id, $this->with);
        return [
            'code' => '200',
            'data' => $this->formatData($data)
        ];
    }

    public function confirm($inputs) {
        if(!isset($inputs['id'])) { return [ 'code' => '003', 'message' => 'Id request' ]; }
        if(!isset($inputs['status'])) { return [ 'code' => '003', 'message' => 'Trạng thái xác nhận' ]; }

        $advertising = $this->repo_base->findById($inputs['id'], $this->with);
        if(!isset($advertising)) { return [ 'code' => '004', 'message' => 'Yêu cầu quảng cáo' ]; }
        if(in_array($advertising->status, [AdvertisingRequest::STATUS_CONFIRM, AdvertisingRequest::STATUS_DESTROY])){
            return [ 'code' => '008', 'message' => 'Yêu cầu quảng cáo' ];
        }

        $employee = Auth::guard('employees')->user();
        if($inputs['status'] == AdvertisingRequest::STATUS_CONFIRM) {
            // create advertiser
            $advertiser = $this->repo_advertiser->findOneBy([
                'user_id' => $advertising->user->id
            ]);
            if(!isset($advertiser)) {
                $advertiser = $this->repo_advertiser->create([
                    'name' => $advertising->user->name,
                    'phone' => $advertising->user->phone,
                    'user_id' => $advertising->user->id,
                    'status' => Advertiser::STATUS_ACTIVE
                ]);
            } else {
                $this->repo_advertiser->update($advertiser->id, [
                    'status' => Advertiser::STATUS_ACTIVE
                ]);
            }
            $advertiser = $this->repo_advertiser->findById($advertiser->id);
            // create campaign
            $campaign = $this->repo_campaign->findOneBy([
                'advertiser_id' => $advertiser->id,
                'status' => Campaign::STATUS_ACTIVE
            ]);
            if(!isset($campaign)) {
                $campaign = $this->repo_campaign->create([
                    'name' => $advertising->user->name,
                    'advertiser_id' => $advertiser->id,
                    'starts_at' => Carbon::now()->toDateString(),
                    'weight' => 1,
                    'status' => Campaign::STATUS_ACTIVE
                ]);
            }
            $zone = $this->repo_zone->findOneBy([
                'type' => $advertising->type
            ]);
            // create banner
            $banner = $this->repo_banner->findOneBy([
                'post_id' => $advertising->post_id,
                'campaign_id' => $campaign->id,
                'status' => Banner::STATUS_ACTIVE
            ]);

            if(!isset($banner)) {
                $banner = $this->repo_banner->create([
                    'name' => $zone->name,
                    'post_id' => $advertising->post_id,
                    'campaign_id' => $campaign->id,
                    'weight' => 1,
                    'status' => Banner::STATUS_ACTIVE
                ]);
            }
            // TODO: xu ly zone banner
            $zone->banners()->sync([$banner->id]);
        }
        // update
        $this->repo_base->update($advertising->id, [
            'status' => $inputs['status']
        ]);

        $advertising = $this->repo_base->findById($advertising->id, $this->with);
        return [
            'code' => '200',
            'data' => $this->formatData($advertising)
        ];
    }

    public function checkInputs($inputs, $id)
    {
        if (isset($inputs['is_app'])) {
            $this->is_app = $inputs['is_app'];
        }
        if(!isset($inputs['post_id'])) { return [ 'code' => '003', 'message' => 'post id' ]; }

        $post = $this->repo_post->findById($inputs['post_id'], []);
        if(!isset($post)) { return [ 'code' => '004', 'message' => 'Bài đăng' ]; }

        if(!isset($inputs['type'])) {
            $inputs['type'] = AdvertisingRequest::TYPE_BOOST;
        }
        if(!isset($inputs['status'])) {
            $inputs['status'] = AdvertisingRequest::STATUS_NEW;
        }
        return [
            'is_failed' => false,
            'inputs' => $inputs
        ];
    }
}
