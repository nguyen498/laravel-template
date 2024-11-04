<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Base\BaseService;
use Carbon\Carbon;

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

    public function checkInputs($inputs, $id)
    {
        if(!isset($inputs['name'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'Name'
            ];
        }
        if(!isset($inputs['type'])){
            $inputs['type'] = Category::HOME_OWNER;
        }
//        if(!isset($inputs['logo'])){
//            return [
//                'code' => '003',
//                'message' => 'Logo'
//            ];
//        }
        $reference = isset($inputs['reference']) && !empty($inputs['reference']) ? $inputs['reference'] : null;
        if(!isset($id)) {
            $inputs['reference'] = $this->generateReference($reference);
        }

        return [
            'is_failed' => false,
            'inputs' => $inputs
        ];
    }

    public function generateReference($reference) {
        if(!isset($reference)) {
            $now = Carbon::now();
            $pre_fix = Category::pre_fix . $now->format('ymd');
            $reference = $this->repo_base->getReferenceByPrefix($pre_fix, 'reference',5, false);
            return "{$reference}";
        }
        return $reference;
    }
}
