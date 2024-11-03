<?php

namespace App\Repositories;

use App\Models\UserInbox;
use App\Repositories\Interfaces\UserInboxRepositoryInterface;
use Carbon\Carbon;

class UserInboxRepository extends BaseRepository implements UserInboxRepositoryInterface
{
    public function getModel()
    {
        return UserInbox::class;
    }

    public function createNotificationSql($notification, $user_id, $user_type = 'users')
    {
        $now = Carbon::now();
        $data = isset($notification->data) ? json_decode($notification->data, true) : [];
        $data['type'] = UserInbox::TYPE_NOTIFICATION;
        return [
            'user_id' => $user_id,
            'user_type' => $user_type,
            'title' => $notification->title,
            'content' => $notification->content,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'type' => UserInbox::TYPE_NOTIFICATION,
            'status' => UserInbox::STATUS_SEND,
            'comment_type' => UserInbox::COMMENT_TYPE_WITHOUT_COMMENT,
            'read_status' => UserInbox::READ_STATUS_NOT_READ,
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ];
    }

    public function findByIdAndType($ids = [], $cond, $select = ['*'])
    {
        $query = $this->model->where($cond);
        if(count($ids) > 0) {
            $query->whereIn('id', $ids);
        }
        return $query->get($select);
    }
}
