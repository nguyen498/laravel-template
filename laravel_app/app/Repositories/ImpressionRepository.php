<?php

namespace App\Repositories;

use App\Models\Impression;
use App\Repositories\Interfaces\ImpressionRepositoryInterface;

class ImpressionRepository extends BaseRepository implements ImpressionRepositoryInterface
{

    public function getModel()
    {
        return Impression::class;
    }
}
