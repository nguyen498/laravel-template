<?php

namespace App\Repositories;

use App\Models\UserGroupChat;
use App\Repositories\Interfaces\UserGroupChatRepositoryInterface;

class UserGroupChatRepository extends BaseRepository implements UserGroupChatRepositoryInterface
{
    public function getModel()
    {
        return UserGroupChat::class;
    }
}
