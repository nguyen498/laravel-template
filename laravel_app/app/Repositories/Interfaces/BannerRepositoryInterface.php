<?php

namespace App\Repositories\Interfaces;

interface BannerRepositoryInterface extends BaseRepositoryInterface
{
    public function findByVisitorId($userId, $min);

    public function getStatsAdvertPost($campaignId, $advertiserId, $searchTitle, $page = 1, $limit = 10);
}
