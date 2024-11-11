<?php

namespace App\Repositories;

use App\Models\Campaign;
use App\Repositories\Interfaces\CampaignRepositoryInterface;

class CampaignRepository extends BaseRepository implements CampaignRepositoryInterface
{

    public function getModel()
    {
        return Campaign::class;
    }
}
