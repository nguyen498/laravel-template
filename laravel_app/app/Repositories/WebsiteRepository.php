<?php

namespace App\Repositories;

use App\Models\Website;
use App\Repositories\Interfaces\WebsiteRepositoryInterface;

class WebsiteRepository extends BaseRepository implements WebsiteRepositoryInterface
{

    public function getModel()
    {
        return Website::class;
    }
}
