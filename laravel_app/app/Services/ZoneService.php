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
    protected $with;

    public function __construct(
        ZoneRepositoryInterface $repo_base,
        ImpressionRepositoryInterface $repo_impression,
        BannerRepositoryInterface $repo_banner
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_impression = $repo_impression;
        $this->repo_banner = $repo_banner;
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
}
