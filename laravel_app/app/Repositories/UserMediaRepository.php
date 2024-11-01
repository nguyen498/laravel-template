<?php

namespace App\Repositories;

use App\Models\UserMedia;
use App\Repositories\Interfaces\UserMediaRepositoryInterface;

class UserMediaRepository extends BaseRepository implements UserMediaRepositoryInterface
{
    public function getModel()
    {
        return UserMedia::class;
    }
}
