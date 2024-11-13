<?php

namespace App\Repositories\Interfaces;

interface ZoneRepositoryInterface extends BaseRepositoryInterface
{
    public function getBanners($zone, $exist_banner_ids);
}
