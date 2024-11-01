<?php

namespace App\Services;

use App\Models\UserInbox;
use App\Repositories\Interfaces\UserInboxRepositoryInterface;
use App\Services\Base\BaseService;

class UserInboxService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        UserInboxRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User inbox';
    }

    public function getTableName()
    {
        return (new UserInbox())->getTable();
    }
}
