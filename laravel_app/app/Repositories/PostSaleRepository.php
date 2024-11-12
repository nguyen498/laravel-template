<?php

namespace App\Repositories;

use App\Models\PostSale;
use App\Repositories\Interfaces\PostSaleRepositoryInterface;

class PostSaleRepository extends BaseRepository implements PostSaleRepositoryInterface
{
    public function getModel()
    {
        return PostSale::class;
    }

    public function deleteByPostIds($postIds)
    {
        $this->model->whereIn('post_id', $postIds)->delete();
    }
}
