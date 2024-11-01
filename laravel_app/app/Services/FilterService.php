<?php

namespace App\Services;

use App\Models\Filter;
use App\Repositories\Interfaces\FilterRepositoryInterface;
use App\Services\Base\BaseService;

class FilterService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        FilterRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Filter';
    }

    public function getTableName()
    {
        return (new Filter())->getTable();
    }
}
