<?php

namespace App\Repositories;

use App\Models\Zone;
use App\Repositories\Interfaces\ZoneRepositoryInterface;

class ZoneRepository extends BaseRepository implements ZoneRepositoryInterface
{

    public function getModel()
    {
        return Zone::class;
    }
}
