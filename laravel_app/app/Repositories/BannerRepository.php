<?php

namespace App\Repositories;

use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BannerRepository extends BaseRepository implements BannerRepositoryInterface
{

    public function getModel()
    {
        return Banner::class;
    }

    public function findByVisitorId($userId, $min)
    {
        $now = Carbon::now()->subMinutes($min);
        $query = $this->model
            ->join('advert_impressions','advert_impressions.banner_id', '=', 'advert_banners.id')
            ->join('advert_imp_visitor_details','advert_imp_visitor_details.impression_id', '=', 'advert_impressions.id')
            ->where('advert_imp_visitor_details.user_id', $userId)
            ->whereRaw('advert_impressions.created_at >= \''. $now->toDateTimeString() .'\'');

        return $query->get(['advert_banners.id', 'advert_banners.post_id']);
    }

    public function getStatsAdvertPost($campaignId, $advertiserId)
    {
        $statistics = DB::table('advert_impressions AS ai')
            ->join('advert_banners AS ab', 'ai.banner_id', '=', 'ab.id')
            ->join('advert_campaigns AS ac', 'ab.campaign_id', '=', 'ac.id')
            ->join('advert_advertisers AS aa', 'ac.advertiser_id', '=', 'aa.id')
            ->join('advert_banner_zone AS abz', 'ab.id', '=', 'abz.banner_id')
            ->join('advert_zones AS az', 'abz.zone_id', '=', 'az.id')
            ->join('posts AS p', 'ab.post_id', '=', 'p.id')
            ->select(
                'az.id AS zone_id',
                'az.name AS zone_name',
                'ac.id AS campaign_id',
                'aa.id AS advertiser_id',
                'p.id AS post_id',
                'p.title AS post_title',
                DB::raw('COUNT(ai.id) AS view_count'),
                DB::raw('SUM(CASE WHEN ai.time_clicked IS NOT NULL THEN 1 ELSE 0 END) AS click_count')
            )
            ->where('ac.id', $campaignId)
            ->where('aa.id', $advertiserId)
            ->groupBy('az.id', 'az.name', 'ac.id', 'aa.id', 'p.id', 'p.title')
            ->orderByDesc('view_count')
            ->orderByDesc('click_count')
            ->get();

        return $statistics;
    }
}
