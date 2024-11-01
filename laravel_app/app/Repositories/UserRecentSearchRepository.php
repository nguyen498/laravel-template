<?php

namespace App\Repositories;

use App\Models\UserRecentSearch;
use App\Repositories\Interfaces\UserRecentSearchRepositoryInterface;

class UserRecentSearchRepository extends BaseRepository implements UserRecentSearchRepositoryInterface
{
    public function getModel()
    {
        return UserRecentSearch::class;
    }
}
