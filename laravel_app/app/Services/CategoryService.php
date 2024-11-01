<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Base\BaseService;

class CategoryService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        CategoryRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Category';
    }

    public function getTableName()
    {
        return (new Category())->getTable();
    }
}
