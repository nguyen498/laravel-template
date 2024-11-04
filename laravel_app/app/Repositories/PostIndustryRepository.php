<?php

namespace App\Repositories;

use App\Models\PostIndustry;
use App\Repositories\Interfaces\PostRepositoryInterface;

class PostIndustryRepository extends BaseRepository implements PostRepositoryInterface
{

    public function getModel()
    {
        return PostIndustry::class;
    }
}
