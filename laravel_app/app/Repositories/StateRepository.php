<?php

namespace App\Repositories;

use App\Models\State;
use App\Repositories\Interfaces\StateRepositoryInterface;

class StateRepository extends BaseRepository implements StateRepositoryInterface
{
    public function getModel()
    {
        return State::class;
    }
}
