<?php

namespace App\Services;

use App\Models\FilterDetail;
use App\Repositories\Interfaces\FilterDetailRepositoryInterface;
use App\Services\Base\BaseService;

class FilterDetailService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        FilterDetailRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'FilterDetail';
    }

    public function getTableName()
    {
        return (new FilterDetail())->getTable();
    }

}
