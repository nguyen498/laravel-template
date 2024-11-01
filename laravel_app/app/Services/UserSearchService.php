<?php

namespace App\Services;

use App\Models\UserSearch;
use App\Repositories\Interfaces\UserSearchRepositoryInterface;
use App\Services\Base\BaseService;

class UserSearchService extends  BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        UserSearchRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User search';
    }

    public function getTableName()
    {
        return (new UserSearch())->getTable();
    }
}
