<?php

namespace App\Repositories;

use App\Models\PostCms;
use App\Repositories\Interfaces\PostCmsRepositoryInterface;

class PostCmsRepository extends BaseRepository implements PostCmsRepositoryInterface
{
    public function getModel()
    {
        return PostCms::class;
    }
}
