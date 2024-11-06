<?php

namespace App\Repositories;

use App\Models\PostCms;
use App\Repositories\Interfaces\PostRepositoryInterface;

class PostCmsRepository extends BaseRepository implements PostRepositoryInterface
{

    public function getModel()
    {
        return PostCms::class;
    }
}
