<?php

namespace App\Services;

use App\Models\PostIndustry;
use App\Repositories\Interfaces\PostIndustryRepositoryInterface;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Services\Base\BaseService;
use Carbon\Carbon;

class PostIndustryService extends BaseService
{
    protected $repo_base;
    protected $repo_sub_category;
    protected $with;

    public function __construct(
        PostIndustryRepositoryInterface $repo_base,
        SubCategoryRepositoryInterface $repo_sub_category,
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_sub_category = $repo_sub_category;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Post industry';
    }

    public function getTableName()
    {
        return (new PostIndustry())->getTable();
    }
    public function checkInputs($inputs, $id)
    {
        if(!isset($inputs['title'])){
            return [
                'code' => '003',
                'is_failed' => true,
                'message' => 'Name'
            ];
        }
        if(!isset($inputs['sub_category_id'])){
            return [
                'code' => '003',
                'is_failed' => true,
                'message' => 'Logo'
            ];
        }
        $sub_category = $this->repo_sub_category->findById($inputs['sub_category_id']);
        if(!isset($sub_category)){
            return [
                'is_failed' => true,
                'code' => '004',
                'message' => 'Sub category'
            ];
        }
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
            $reference = $this->repo_base->getReferenceByPrefix($now->format('ymd'), 'reference',5, false);
            $pre_fix = PostIndustry::pre_fix;
            return "{$pre_fix}{$reference}";
        }
        return $reference;
    }
}
