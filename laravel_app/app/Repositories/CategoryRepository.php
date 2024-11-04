<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Utils\StringHelpers;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{

    public function getModel()
    {
        return Category::class;
    }

    public function getReference()
    {
        $data_codes = $this->model->get(['reference']);
        $dict_code = [];
        foreach($data_codes as $code) {
            if(!isset($dict_code[$code->reference])) {
                $dict_code[$code->reference] = $code->reference;
            }
        }
        $random = null;
        $length = config('enums.code_length.employee') ?? 10;
        while(!isset($random)) {
            $ran = StringHelpers::generateRandomString($length);
            if(!isset($dict_code[$ran])) {
                $random = $ran;
            }
        }
        return $random;
    }
}
