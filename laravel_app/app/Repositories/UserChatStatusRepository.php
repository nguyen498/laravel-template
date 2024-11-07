<?php

namespace App\Repositories;

use App\Models\UserChatStatus;
use App\Repositories\Interfaces\UserChatStatusRepositoryInterface;

class UserChatStatusRepository extends BaseRepository implements UserChatStatusRepositoryInterface
{
    public function getModel()
    {
        return UserChatStatus::class;
    }

}
