<?php

namespace App\Services;

use App\Models\Config;
use App\Repositories\Interfaces\ConfigRepositoryInterface;
use App\Services\Base\BaseService;

class ConfigService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        ConfigRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Config';
    }

    public function getTableName()
    {
        return (new Config())->getTable();
    }
}
