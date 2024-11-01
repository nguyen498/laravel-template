<?php

namespace App\Services;

use App\Models\Device;
use App\Repositories\Interfaces\DeviceRepositoryInterface;
use App\Services\Base\BaseService;

class DeviceService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        DeviceRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Device';
    }

    public function getTableName()
    {
        return (new Device())->getTable();
    }
}
