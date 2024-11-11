<?php

namespace App\Repositories;

use App\Models\City;
use App\Repositories\Interfaces\CityRepositoryInterface;

class CityRepository extends BaseRepository implements CityRepositoryInterface
{

    public function getModel()
    {
        return City::class;
    }
}
