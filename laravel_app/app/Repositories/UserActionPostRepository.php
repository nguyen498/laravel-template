<?php

namespace App\Repositories;

use App\Models\UserActionPost;
use App\Repositories\Interfaces\UserActionPostRepositoryInterface;

class UserActionPostRepository extends BaseRepository implements UserActionPostRepositoryInterface
{
    public function getModel()
    {
        return UserActionPost::class;
    }
}
