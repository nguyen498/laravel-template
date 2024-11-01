<?php

namespace App\Services;

use App\Models\Notification;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Services\Base\BaseService;

class NotificationService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        NotificationRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Notification';
    }

    public function getTableName()
    {
        return (new Notification())->getTable();
    }
}
