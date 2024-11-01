<?php

namespace App\Repositories;

use App\Models\UserChat;
use App\Repositories\Interfaces\UserChatRepositoryInterface;

class UserChatRepository extends BaseRepository implements UserChatRepositoryInterface
{
    public function getModel()
    {
        return UserChat::class;
    }
}
