<?php

namespace App\Repositories;

use App\Models\PostIndustry;
use App\Repositories\Interfaces\PostIndustryRepositoryInterface;

class PostIndustryRepository extends BaseRepository implements PostIndustryRepositoryInterface
{

    public function getModel()
    {
        return PostIndustry::class;
    }
}
