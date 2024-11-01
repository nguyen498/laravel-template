<?php

namespace App\Repositories;

use App\Models\Address;
use App\Repositories\Interfaces\AddressRepositoryInterface;

class AddressRepository extends BaseRepository implements AddressRepositoryInterface
{

    public function getModel()
    {
        return Address::class;
    }
}
