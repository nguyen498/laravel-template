<?php

namespace App\Repositories\Interfaces;

interface UserInboxRepositoryInterface extends BaseRepositoryInterface
{
    public function createNotificationSql($notification, $user_id, $user_type = 'users');

    public function findByIdAndType($ids = [], $cond, $select = ['*']);
}
