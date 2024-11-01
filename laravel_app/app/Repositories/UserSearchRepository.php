<?php

namespace App\Repositories;

use App\Models\UserSearch;
use App\Repositories\Interfaces\UserSearchRepositoryInterface;

class UserSearchRepository extends BaseRepository implements UserSearchRepositoryInterface
{
    public function getModel()
    {
        return UserSearch::class;
    }
}
