<?php

namespace App\Repositories;

use App\Models\FilterDetail;
use App\Repositories\Interfaces\FilterDetailRepositoryInterface;

class FilterDetailRepository extends BaseRepository implements FilterDetailRepositoryInterface
{

    public function getModel()
    {
        return FilterDetail::class;
    }
}
