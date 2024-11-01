<?php

namespace App\Services;

use App\Models\AdvertisingRequest;
use App\Repositories\Interfaces\AdvertisingRequestRepositoryInterface;
use App\Services\Base\BaseService;

class AdvertisingRequestService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        AdvertisingRequestRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Advertising Request';
    }

    public function getTableName()
    {
        return (new AdvertisingRequest())->getTable();
    }
}
