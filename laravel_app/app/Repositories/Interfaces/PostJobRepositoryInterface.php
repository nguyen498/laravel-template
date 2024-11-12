<?php

namespace App\Repositories\Interfaces;

interface PostJobRepositoryInterface extends BaseRepositoryInterface
{
    public function deleteByPostIds($postIds);
}
