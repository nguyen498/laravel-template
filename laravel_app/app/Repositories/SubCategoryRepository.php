<?php

namespace App\Repositories;

use App\Models\SubCategory;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;

class SubCategoryRepository extends BaseRepository implements SubCategoryRepositoryInterface
{
    public function getModel()
    {
        return SubCategory::class;
    }
}
