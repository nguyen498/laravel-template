<?php

namespace App\Services;

use App\Models\UserGroupChat;
use App\Repositories\Interfaces\UserGroupChatRepositoryInterface;
use App\Services\Base\BaseService;

class UserGroupChatService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        UserGroupChatRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User group chat';
    }

    public function getTableName()
    {
        return (new UserGroupChat())->getTable();
    }
}
