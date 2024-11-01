<?php

namespace App\Services;

use App\Models\UserPost;
use App\Repositories\Interfaces\UserPostRepositoryInterface;
use App\Services\Base\BaseService;

class UserPostService extends  BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        UserPostRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User post';
    }

    public function getTableName()
    {
        return (new UserPost())->getTable();
    }
}
