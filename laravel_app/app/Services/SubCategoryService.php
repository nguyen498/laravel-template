<?php

namespace App\Services;

use App\Models\State;
use App\Models\SubCategory;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Services\Base\BaseService;

class SubCategoryService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        SubCategoryRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Sub category';
    }

    public function getTableName()
    {
        return (new SubCategory())->getTable();
    }
}
