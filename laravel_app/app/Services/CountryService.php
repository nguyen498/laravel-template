<?php

namespace App\Services;

use App\Models\Country;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Services\Base\BaseService;

class CountryService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        CountryRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Country';
    }

    public function getTableName()
    {
        return (new Country())->getTable();
    }
}
