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
}
