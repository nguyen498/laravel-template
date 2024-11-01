<?php

namespace App\Repositories;

use App\Models\UserComment;
use App\Repositories\Interfaces\UserCommentRepositoryInterface;

class UserCommentRepository extends BaseRepository implements UserCommentRepositoryInterface
{
    public function getModel()
    {
        return UserComment::class;
    }
}
