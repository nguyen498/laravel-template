<?php

namespace App\Services;

use App\Models\VisitorDetail;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Repositories\Interfaces\ImpressionRepositoryInterface;
use App\Repositories\Interfaces\VisitorDetailRepositoryInterface;
use App\Repositories\Interfaces\ZoneRepositoryInterface;
use App\Services\Base\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VisitorDetailService extends  BaseService
{
    protected $repo_base;
    protected $repo_banner;
    protected $repo_impression;
    protected $repo_zone;
    protected $with;

    public function __construct(
        VisitorDetailRepositoryInterface $repo_base,
        ImpressionRepositoryInterface $repo_impression,
        BannerRepositoryInterface $repo_banner,
        ZoneRepositoryInterface $repo_zone
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_banner = $repo_banner;
        $this->repo_impression = $repo_impression;
        $this->repo_zone = $repo_zone;
        $this->with = ['impression'];
    }

    public function getModelName()
    {
        return 'Visitor detail';
    }

    public function getTableName()
    {
        return (new VisitorDetail())->getTable();
    }

    public function setViewClick($inputs) {
        if(!isset($inputs['banner_id'])) { return [ 'code' => '003', 'message' => 'Bài banner' ]; }
        if(!isset($inputs['type'])) { return [ 'code' => '003', 'message' => 'Zone' ]; }

        $banner = $this->repo_banner->findById($inputs['banner_id']);
        if(!isset($banner)) { return [ 'code' => '004', 'message' => 'Bài banner' ]; }
        $zone = $this->repo_zone->findOneBy([
            'type' =>$inputs['type']
        ]);
        if(!isset($zone)) { return [ 'code' => '004', 'message' => 'Zone quảng cáo' ]; }
        $user = Auth::guard('users')->user();
        // create from impression first
        $ins_inputs = [
            'banner_id' => $banner->id,
            'zone_id' => $zone->id,
        ];
        $impression = $this->repo_impression->findLatestByConds($ins_inputs);
        $now = Carbon::now();
        // viewed
        if(!isset($impression)) {
            $ins_inputs['clicked'] = false;
            $ins_inputs['time_viewed'] = Carbon::now()->toDateTimeString();
            $impression = $this->repo_impression->create($ins_inputs);
        } else {
            // case to check again for impression
            if($impression->clicked) {
                $created_at = $impression->created_at;
                $total_min = $now->diffInMinutes($created_at, true);
                // create new if available time for visit rule
                if($total_min >= config('advertiser_config.visit_rule')) {
                    $ins_inputs['clicked'] = false;
                    $ins_inputs['time_viewed'] = Carbon::now()->toDateTimeString();
                    $impression = $this->repo_impression->create($ins_inputs);
                }
            } else {
                $ins_inputs['clicked'] = true;
                $ins_inputs['time_clicked'] = Carbon::now()->toDateTimeString();
                $this->repo_impression->update($impression->id, $ins_inputs);
            }
        }
        $impression = $this->repo_impression->findById($impression->id);
        $ins_vit_details = [
            'impression_id' => $impression->id,
            'user_id' => $user->id
        ];
        // create visitor
        $visitor_detail = $this->repo_base->findOneBy($ins_vit_details);
        if(!isset($visitor_detail)) {
            $visitor_detail = $this->repo_base->create($ins_vit_details);
        }

        $visitor_detail = $this->repo_base->findById($visitor_detail->id, $this->with);
        return [
            'code' => '200',
            'data' => $this->formatData($visitor_detail)
        ];
    }
}
