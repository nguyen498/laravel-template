<?php

namespace App\Repositories\Interfaces;

interface ImpressionRepositoryInterface extends BaseRepositoryInterface
{
    public function findLatestByConds(array $conds, $orderBy = 'created_at');
}
