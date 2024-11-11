<?php

namespace App\Repositories;

use App\Models\Advertiser;
use App\Repositories\Interfaces\AdvertiserRepositoryInterface;

class AdvertiserRepository extends BaseRepository implements AdvertiserRepositoryInterface
{

    public function getModel()
    {
        return Advertiser::class;
    }
}
