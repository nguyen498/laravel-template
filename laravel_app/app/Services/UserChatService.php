<?php

namespace App\Services;

use App\Models\UserChat;
use App\Repositories\Interfaces\UserChatRepositoryInterface;
use App\Services\Base\BaseService;

class UserChatService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        UserChatRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User chat';
    }

    public function getTableName()
    {
        return (new UserChat())->getTable();
    }
}
