<?php

namespace App\Repositories;

use App\Models\UserGroupChatStatus;
use App\Repositories\Interfaces\UserGroupChatStatusRepositoryInterface;

class UserGroupChatStatusRepository extends BaseRepository implements UserGroupChatStatusRepositoryInterface
{
    public function getModel()
    {
        return UserGroupChatStatus::class;
    }
}
