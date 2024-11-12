<?php

namespace App\Repositories;

use App\Models\Impression;
use App\Models\VisitorDetail;
use App\Repositories\Interfaces\ImpressionRepositoryInterface;
use Carbon\Carbon;

class ImpressionRepository extends BaseRepository implements ImpressionRepositoryInterface
{

    public function getModel()
    {
        return Impression::class;
    }

    public function findLatestByConds(array $conds, $orderBy = 'created_at')
    {
        $data = $this->model->where($conds)
            ->orderBy($orderBy, 'desc')
            ->limit(1)->get();
        return count($data) > 0 ? $data[0] : null;
    }
}
