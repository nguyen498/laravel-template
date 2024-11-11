<?php

namespace App\Services;

use App\Models\City;
use App\Repositories\Interfaces\CityRepositoryInterface;
use App\Services\Base\BaseService;

class CityService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        CityRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'City';
    }

    public function getTableName()
    {
        return (new City())->getTable();
    }
}
