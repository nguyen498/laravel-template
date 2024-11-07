<?php

namespace App\Services;

use App\Models\UserChatStatus;
use App\Models\UserGroupChat;
use App\Models\UserGroupChatStatus;
use App\Repositories\Interfaces\UserChatStatusRepositoryInterface;
use App\Repositories\Interfaces\UserGroupChatRepositoryInterface;
use App\Repositories\Interfaces\UserGroupChatStatusRepositoryInterface;
use App\Services\Base\BaseService;
use App\Utils\SqlUtil;
use Illuminate\Support\Facades\Auth;

class UserGroupChatService extends BaseService
{
    protected $repo_base;
    protected $repo_user_group_chat_status;
    protected $repo_user_chat_status;
    protected $with;

    public function __construct(
        UserGroupChatRepositoryInterface $repo_base,
        UserGroupChatStatusRepositoryInterface $repo_user_group_chat_status,
        UserChatStatusRepositoryInterface $repo_user_chat_status
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_user_group_chat_status = $repo_user_group_chat_status;
        $this->repo_user_chat_status = $repo_user_chat_status;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User group chat';
    }

    public function getTableName()
    {
        return (new UserGroupChat())->getTable();
    }

    public function deleteGroupChat($id){
        $user = Auth::guard('users')->user();

        $data = $this->repo_base->findById($id);
        if(!isset($data)){
            return [
                'is_failed' => true,
                'code' => '004',
                'message' => 'Group chat'
            ];
        }
        UserGroupChatStatus::where('user_group_chat_id', $data->id)
            ->where('user_id', $user->id)
            ->update([
                'status' => UserChatStatus::STATUS_UNACTIVE
            ]);

        UserChatStatus::where('user_group_chat_id', $data->id)
            ->where('user_id', $user->id)
            ->update([
                'status' => UserChatStatus::STATUS_UNACTIVE
            ]);

        return [
            'code' => '200',
            'data' => []
        ];
    }

    public function searchApp($inputs){
        $user = Auth::guard('users')->user();

        $groups = $this->repo_user_group_chat_status->findWhereBy([
            'user_id' => $user->id,
            'status' => UserGroupChatStatus::STATUS_ACTIVE
        ]);
        $ids = [];
        foreach ($groups as $group){
            array_push($ids, $group->user_group_chat_id);
        }
        if(count($ids) === 0){
            return [
                "code" => '200',
                'data' => [
                    "data" => [],
                    'total' => 0
                ]
            ];
        }

        $inputs['filter']['ids'] = $ids;

        return $this->search($inputs);
    }

    public function generateColumn($inputs, $columns)
    {
        $sqlUtil = new SqlUtil();
        if(isset($inputs['ids']) && count($inputs['ids']) > 0 && $inputs['ids'] !== 'all'){
            array_push($columns, "{$this->getTableName()}.id in ({$sqlUtil->setStringFromArray($inputs['ids'])})");
        }
        return $columns;
    }
}
