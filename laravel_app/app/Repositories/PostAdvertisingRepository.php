<?php

namespace App\Repositories;

use App\Models\PostAdvertising;
use App\Repositories\Interfaces\PostAdvertisingRepositoryInterface;

class PostAdvertisingRepository extends BaseRepository implements PostAdvertisingRepositoryInterface
{

    public function getModel()
    {
       return PostAdvertising::class;
    }
}
