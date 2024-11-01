<?php

namespace App\Repositories;

use App\Models\UserInbox;
use App\Repositories\Interfaces\UserInboxRepositoryInterface;

class UserInboxRepository extends BaseRepository implements UserInboxRepositoryInterface
{
    public function getModel()
    {
        return UserInbox::class;
    }
}
