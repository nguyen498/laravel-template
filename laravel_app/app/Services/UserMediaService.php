<?php

namespace App\Services;

use App\Models\UserMedia;
use App\Repositories\Interfaces\UserMediaRepositoryInterface;
use App\Services\Base\BaseService;

class UserMediaService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        UserMediaRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User media';
    }

    public function getTableName()
    {
        return (new UserMedia())->getTable();
    }
}
