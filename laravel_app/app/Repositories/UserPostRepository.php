<?php

namespace App\Repositories;

use App\Models\UserPost;
use App\Repositories\Interfaces\UserPostRepositoryInterface;

class UserPostRepository extends BaseRepository implements UserPostRepositoryInterface
{
    public function getModel()
    {
        return UserPost::class;
    }
}
