<?php

namespace App\Services;

use App\Models\UserChat;
use App\Models\UserChatStatus;
use App\Models\UserGroupChatStatus;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\UserChatRepositoryInterface;
use App\Repositories\Interfaces\UserChatStatusRepositoryInterface;
use App\Repositories\Interfaces\UserGroupChatRepositoryInterface;
use App\Repositories\Interfaces\UserGroupChatStatusRepositoryInterface;
use App\Services\Base\BaseService;
use App\Utils\SqlUtil;
use Illuminate\Support\Facades\Auth;

class UserChatService extends BaseService
{
    protected $repo_base;
    protected $repo_post;
    protected $repo_user_group_chat;
    protected $repo_user_chat_status;
    protected $repo_user_group_chat_status;
    protected $with;

    public function __construct(
        UserChatRepositoryInterface $repo_base,
        PostRepositoryInterface $repo_post,
        UserGroupChatRepositoryInterface $repo_user_group_chat,
        UserGroupChatStatusRepositoryInterface $repo_user_group_chat_status,
        UserChatStatusRepositoryInterface $repo_user_chat_status
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_post = $repo_post;
        $this->repo_user_group_chat = $repo_user_group_chat;
        $this->repo_user_chat_status = $repo_user_chat_status;
        $this->repo_user_group_chat_status = $repo_user_group_chat_status;
        $this->with = ['actor'];
    }

    public function getModelName()
    {
        return 'User chat';
    }

    public function getTableName()
    {
        return (new UserChat())->getTable();
    }

    public function sendMessage($inputs){
        $user = Auth::guard('users')->user();
        $validate = $this->checkInputs($inputs, null);
        if ($validate['is_failed']) {
            return $validate;
        }
        $inputs = $validate['inputs'];
        $post = $this->repo_post->findById($inputs['post_id']);
        if(!isset($post)){
            return [
                'code' => '004',
                'message' => 'Post'
            ];
        }
        if($user->id === $post->user_id){
            return [
                'code' => '008',
                'message' => 'User'
            ];
        }

        $user_group_chat = $this->getUserGroupChat($post, $user->id);

        $data = $this->createUserChat($user_group_chat,$user->id, $inputs);

        $data = $this->repo_base->findById($data->id);

        return [
            'code' => '200',
            'data' => $this->formatData($data)
        ];
    }

    public function deleteMessage($id){
        $user = Auth::guard('users')->user();
//        $data = $this->repo_base->findById($id);
//        if (!isset($data)) {
//            return ['code' => '004', 'message' => $this->getModelName()];
//        }
        $data =$this->repo_user_chat_status->findOneBy([
            'message_id' => $id,
            'user_id' => $user->id
        ]);
        if(isset($data)){
            $data->update([
                'status' => UserChatStatus::STATUS_UNACTIVE
            ]);
        }

        return [
            'code' => '200',
            'data' => []
        ];
    }

    public function getUserGroupChat($post, $user_id){
        $user_group_chat = $this->repo_user_group_chat->findOneBy([
            'post_id' => $post->id,
            'actor_id' => $user_id
        ]);

        if(!isset($user_group_chat)){
            $user_group_chat = $this->repo_user_group_chat->create([
                'actor_id' =>  $user_id,
                'post_id' => $post->id,
                'user_id' => $post->user_id,
                'title' => $post->title
            ]);
            UserGroupChatStatus::create([
                'user_group_chat_id' => $user_group_chat->id,
                'user_id' => $user_id,
                'status' => UserGroupChatStatus::STATUS_ACTIVE
            ]);

            UserGroupChatStatus::create([
                'user_group_chat_id' => $user_group_chat->id,
                'user_id' => $post->user_id,
                'status' => UserGroupChatStatus::STATUS_ACTIVE
            ]);
        }

        $num_message_not_read_user = 0;
        $num_message_not_read_actor = 0;
        if($post->user_id === $user_id){
            $num_message_not_read_actor = $user_group_chat->num_message_not_read_actor + 1;
        }else{
            $num_message_not_read_user = $user_group_chat->num_message_not_read_user + 1;

        }
        $this->repo_base->update($user_group_chat->id, [
            'num_message_not_read_user' => $num_message_not_read_user,
            'num_message_not_read_actor' => $num_message_not_read_actor
        ]);
        $user_group_chat_status = $this->repo_user_group_chat_status->findOneBy([
            'user_group_chat_id' => $user_group_chat->id,
            'user_id' => $user_id,
            'status' => UserGroupChatStatus::STATUS_UNACTIVE
        ]);
        if(isset($user_group_chat_status)){
            $user_group_chat_status->update([
                'status' => UserGroupChatStatus::STATUS_ACTIVE
            ]);
        }
        return $user_group_chat;
    }

    public function createUserChat($user_group_chat,$user_id, $inputs){
        $data = $this->repo_base->create([
            'user_group_chat_id' => $user_group_chat->id,
            'actor_id' => $user_id,
            'message' => $inputs['message'],
            'medias' => isset($inputs['medias']) ? json_encode($inputs['medias']) : null
        ]);
        UserChatStatus::create([
            'user_group_chat_id' => $user_group_chat->id,
            'message_id' => $data->id,
            'user_id' => $user_group_chat->actor_id,
            'status' => UserChatStatus::STATUS_ACTIVE
        ]);
        UserChatStatus::create([
            'user_group_chat_id' => $user_group_chat->id,
            'message_id' => $data->id,
            'user_id' => $user_group_chat->user_id,
            'status' => UserChatStatus::STATUS_ACTIVE
        ]);
        return $data;
    }

    public function checkInputs($inputs, $id){
        if(!isset($inputs['message'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'message'
            ];
        }

        if(!isset($inputs['post_id'])){
            return [
                'is_failed' => true,
                'code' => '003',
                'message' => 'post'
            ];
        }

        return [
            'is_failed' => false,
            'inputs' => $inputs
        ];
    }

    public function searchApp($inputs){
        $user = Auth::guard('users')->user();
        $user_group_chat = $this->repo_user_group_chat->findOneBy(['post_id' => $inputs['filter']['post_id']]);
        if(!isset($user_group_chat) || !isset($user)){
            return [
                "code" => '200',
                'data' => [
                    "data" => [],
                    'total' => 0
                ]
            ];
        }
        $messages = $this->repo_user_chat_status->findWhereBy([
            'user_group_chat_id' =>$user_group_chat->id,
            'user_id' => $user->id,
            'status' => UserChatStatus::STATUS_ACTIVE
        ]);
        $ids = [];
        foreach ($messages as $message){
            array_push($ids, $message->message_id);
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

        $inputs['filter']['user_group_chat_id'] = $user_group_chat->id;
        $inputs['filter']['ids'] = $ids;
        return $this->search($inputs);
    }

    public function generateColumn($inputs, $columns)
    {
        $sqlUtil = new SqlUtil();
        if(isset($inputs['user_group_chat_id']) && $inputs['user_group_chat_id'] !== 'all'){
            array_push($columns, "{$this->getTableName()}.user_group_chat_id = '{$inputs['user_group_chat_id']}'");
        }
        if(isset($inputs['ids']) && count($inputs['ids']) > 0 && $inputs['ids'] !== 'all'){
            array_push($columns, "{$this->getTableName()}.id in ({$sqlUtil->setStringFromArray($inputs['ids'])})");
        }
        return $columns;
    }
}
