<?php

namespace App\Repositories;

use App\Models\AdvertisingRequest;
use App\Repositories\Interfaces\AdvertisingRequestRepositoryInterface;

class AdvertisingRequestRepository extends BaseRepository implements AdvertisingRequestRepositoryInterface
{

    public function getModel()
    {
        return AdvertisingRequest::class;
    }
}
