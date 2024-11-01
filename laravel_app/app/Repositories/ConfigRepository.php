<?php

namespace App\Repositories;

use App\Models\Config;
use App\Repositories\Interfaces\ConfigRepositoryInterface;

class ConfigRepository extends BaseRepository implements ConfigRepositoryInterface
{

    public function getModel()
    {
        return Config::class;
    }
}
