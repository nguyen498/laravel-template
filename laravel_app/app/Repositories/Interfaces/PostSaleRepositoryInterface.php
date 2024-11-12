<?php

namespace App\Repositories\Interfaces;

interface PostSaleRepositoryInterface extends BaseRepositoryInterface
{
    public function deleteByPostIds($postIds);
}
