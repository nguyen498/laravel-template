<?php

namespace App\Services;

use App\Models\UserChat;
use App\Models\UserGroupChatStatus;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\UserChatRepositoryInterface;
use App\Repositories\Interfaces\UserGroupChatRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Support\Facades\Auth;

class UserChatService extends BaseService
{
    protected $repo_base;
    protected $repo_post;
    protected $repo_user_group_chat;
    protected $with;

    public function __construct(
        UserChatRepositoryInterface $repo_base,
        PostRepositoryInterface $repo_post,
        UserGroupChatRepositoryInterface $repo_user_group_chat
    )
    {
        $this->repo_base = $repo_base;
        $this->repo_post = $repo_post;
        $this->repo_user_group_chat = $repo_user_group_chat;
        $this->with = [];
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
        $user = Auth::user();
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

        $data = $this->repo_base->create([
            'user_group_chat' => $user_group_chat->id,
            'message' => $inputs['message'],
            'medias' => $inputs['medias'] ?? null
        ]);
        $data = $this->repo_base->findById($data->id);

        return [
            'code' => '200',
            'data' => $this->formatData($data)
        ];
    }

    public function deleteMessage($id){

    }

    public function searchApp($id){

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
                'user_id' => $post->user_id
            ]);
            UserGroupChatStatus::create([
                'room_id' => $user_group_chat->id,
                'user_id' => $user_id,
                'is_deleted' => false
            ]);

            UserGroupChatStatus::create([
                'room_id' => $user_group_chat->id,
                'user_id' => $post->user_id,
                'is_deleted' => false
            ]);
        }
        return $user_group_chat;
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

    public function generateColumn($inputs, $columns)
    {
        if(isset($inputs['user_group_chat_id'])){
            array_push($columns, "{$this->getTableName()}.user_group_chat_id = '{$inputs['user_group_chat_id']}'");
        }
        return $columns;
    }
}
