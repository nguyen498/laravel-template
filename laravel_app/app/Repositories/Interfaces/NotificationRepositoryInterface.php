<?php

namespace App\Repositories\Interfaces;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    public function findNotificationByWhere($status, $from, $to);

    public function findNotificationDay();

    public function findNotificationWeek();

    public function findNotificationMonth();
}
