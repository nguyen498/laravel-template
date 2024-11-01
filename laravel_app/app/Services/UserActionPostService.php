<?php

namespace App\Services;

use App\Models\UserActionPost;
use App\Repositories\Interfaces\UserActionPostRepositoryInterface;
use App\Services\Base\BaseService;

class UserActionPostService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        UserActionPostRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User action post';
    }

    public function getTableName()
    {
        return (new UserActionPost())->getTable();
    }
}
