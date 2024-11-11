<?php

namespace App\Repositories;

use App\Models\VisitorDetail;
use App\Repositories\Interfaces\VisitorDetailRepositoryInterface;

class VisitorDetailRepository extends BaseRepository implements VisitorDetailRepositoryInterface
{

    public function getModel()
    {
        return VisitorDetail::class;
    }
}
