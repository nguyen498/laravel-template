<?php

namespace App\Services;

use App\Models\State;
use App\Models\SubCategory;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Services\Base\BaseService;

class SubCategoryService extends BaseService
{
    protected $repo_base;
    protected $repo_category;
    protected $with;

    public function __construct(
        SubCategoryRepositoryInterface $repo_base,
        CategoryRepositoryInterface $repo_category
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_category = $repo_category;
        $this->with = [
            'category'
        ];
    }

    public function getModelName()
    {
        return 'Sub category';
    }

    public function getTableName()
    {
        return (new SubCategory())->getTable();
    }

    public function checkInputs($inputs, $id)
    {
        if(!isset($inputs['name'])){
            return [
               'is_failed' => true,
                'code' => '003',
                'message' => 'Name'
            ];
        }
        if(!isset($inputs['logo'])){
            return [
               'is_failed' => true,
                'code' => '003',
                'message' => 'Logo'
            ];
        }
        if(!isset($inputs['category_id'])){
            return [
               'is_failed' => true,
                'code' => '003',
                'message' => 'Logo'
            ];
        }
        $category = $this->repo_category->findById($inputs['category_id']);
        if(!isset($category)){
            return [
                'is_failed' => true,
                'code' => '004',
                'message' => 'Category'
            ];
        }

        return [
            'is_failed' => false,
            'inputs' => $inputs
        ];
    }

    public function generateColumn($inputs, $columns)
    {
        if(isset($inputs['category_id']) && $inputs['category_id'] !== 'all'){
            array_push($columns, $this->getTableName() . ".category_id = '{$inputs['category_id']}'");
        }
        return $columns;
    }
}
