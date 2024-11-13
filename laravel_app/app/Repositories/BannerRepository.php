<?php

namespace App\Repositories;

use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Carbon\Carbon;

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
}
