<?php

namespace App\Services;

use App\Models\PostAdvertising;
use App\Repositories\Interfaces\PostAdvertisingRepositoryInterface;
use App\Services\Base\BaseService;

class PostAdvertisingService extends BaseService
{

    protected $repo_base;
    protected $with;

    public function __construct(
        PostAdvertisingRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Post advertising';
    }

    public function getTableName()
    {
        return (new PostAdvertising())->getTable();
    }
}
