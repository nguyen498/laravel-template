<?php

namespace App\Repositories;

use App\Models\Filter;
use App\Repositories\Interfaces\FilterRepositoryInterface;

class FilterRepository extends BaseRepository implements FilterRepositoryInterface
{

    public function getModel()
    {
        return Filter::class;
    }
}
