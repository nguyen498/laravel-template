<?php

namespace App\Services;

use App\Models\UserRecentSearch;
use App\Repositories\Interfaces\UserRecentSearchRepositoryInterface;
use App\Services\Base\BaseService;

class UserRecentSearchService extends  BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        UserRecentSearchRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User recent search';
    }

    public function getTableName()
    {
        return (new UserRecentSearch())->getTable();
    }
}
