<?php

namespace App\Repositories;

use App\Models\Zone;
use App\Repositories\Interfaces\ZoneRepositoryInterface;

class ZoneRepository extends BaseRepository implements ZoneRepositoryInterface
{

    public function getModel()
    {
        return Zone::class;
    }

    public function getBanners($zone, $exist_banner_ids)
    {
        $query = $zone->banners()
            ->orderByRaw('rand()');
        if(count($exist_banner_ids) > 0) {
            $query->whereNotIn('banner_id', $exist_banner_ids);
        }
        return $query->get();
    }
}
