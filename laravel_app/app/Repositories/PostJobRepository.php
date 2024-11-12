<?php

namespace App\Repositories;

use App\Models\PostJob;
use App\Repositories\Interfaces\PostJobRepositoryInterface;

class PostJobRepository extends BaseRepository implements PostJobRepositoryInterface
{
    public function getModel()
    {
        return PostJob::class;
    }

    public function deleteByPostIds($postIds)
    {
        $this->model->whereIn('post_id', $postIds)->delete();
    }
}
