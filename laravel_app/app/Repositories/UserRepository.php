<?php


namespace App\Repositories;


use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Utils\StringHelpers;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function getModel()
    {
        return User::class;
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
        $length = config('enums.code_length.passenger') ?? 10;
        while(!isset($random)) {
            $ran = StringHelpers::generateRandomNumber($length);
            if(!isset($dict_code[$ran])) {
                $random = $ran;
            }
        }
        return $random;
    }
}
