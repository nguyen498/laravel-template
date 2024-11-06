<?php


namespace App\Services;


use App\Constants\PolymorphyMap;
use App\Constants\QueueMap;
use App\Jobs\ProcessSendNotificationInboxJob;
use App\Jobs\ProcessSendNotificationInboxOneTimeJob;
use App\Jobs\ProcessSendNotificationOneTimeJob;
use App\Models\Notification;
use App\Models\UserInbox;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\UserInboxRepositoryInterface;
use App\Services\Base\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserInboxService extends BaseService
{
    protected $repo_base;
    protected $repo_notification;
    protected $service_send_notification;
    protected $with;

    public function __construct(
        UserInboxRepositoryInterface $repo_base,
        NotificationRepositoryInterface $repo_notification,
        SendNotificationService $service_send_notification
    ) {
        $this->repo_base                    = $repo_base;
        $this->repo_notification            = $repo_notification;
        $this->service_send_notification    = $service_send_notification;
        $this->with                         = [];
    }

    public function getModelName()
    {
        return 'Inbox';
    }

    public function getTableName()
    {
        return (new UserInbox())->getTable();
    }

//    public function searchApp($inputs) {
//        $this->is_app = true;
//        $auth = $this->getAuthInputs($inputs);
//        $inputs['filter']['user_id'] = $auth['user_id'];
//        $inputs['filter']['user_type'] = $auth['user_type'];
//        return $this->search($inputs);
//    }

    public function updateRead($id, $inputs) {
        $user = Auth::user();
        $data = $this->repo_base->findOneBy([
            'user_id' => $user->id,
            'id' => $id
        ]);
        if (!isset($data)) {
            return ['code' => '004', 'message' => $this->getModelName()];
        }
        if(!isset($inputs['read_status'])){
            return [ 'code' => '003', 'message' => 'status' ];
        }
        $this->repo_base->update($id, $inputs);

        return [
            'code' => '200',
            'data' => [],
            'message' => 'update successful'
        ];
    }

    public function deleteInbox($id) {
        $user = Auth::user();
        $data = $this->repo_base->findOneBy([
            'user_id' => $user->id,
            'id' => $id
        ]);
        if (!isset($data)) {
            return ['code' => '004', 'message' => $this->getModelName()];
        }
        $this->repo_base->delete($id);

        return [
            'code' => '200',
            'data' => [],
            'message' => 'delete successful'
        ];
    }

    public function updateReadAll() {
        $user = Auth::user();
        UserInbox::where('user_id', $user->id)
            ->where('read_status', UserInbox::READ_STATUS_NOT_READ)
            ->update(['read_status' => UserInbox::READ_STATUS_READ]);

        return [
            'code' => '200',
            'data' => [],
            'message' => 'update successful'
        ];
    }

    public function searchApp($inputs){
        $user = Auth::user();
        $inputs['filter']['user_id'] = $user->id;
        return $this->search($inputs);
    }

    public function sendCommentNotificationToPostOwner($inputs){
        $inputs['type'] = UserInbox::TYPE_COMMENT_TO_POST_OWNER;
        if(!isset($inputs['user_id'])) {
            return [ 'code' => '003', 'message' => 'Id user' ];
        }
//        if(!isset($inputs['user_type'])) { $inputs['user_type'] = PolymorphyMap::USER; }
//        if(!isset($inputs['comment_type'])) { $inputs['comment_type'] = UserInbox::COMMENT_TYPE_WITHOUT_COMMENT; }
        if(!isset($inputs['read_status'])) { $inputs['read_status'] = UserInbox::READ_STATUS_NOT_READ; }
        if(!isset($inputs['status'])) { $inputs['status'] = UserInbox::STATUS_NEW; }

        $inputs['title'] = $this->generateTitleWithType($inputs['type'],[
            'user_name' => $inputs['actor_name']
        ]);
        if(!isset($inputs['title'])) {
            return [ 'code' => '008', 'message' => 'Loại thông báo' ];
        }
        $inputs['data'] = [
            'post_id' => $inputs['post_id'],
            'type' => $inputs['type']
        ];

        $inputs['content'] = $this->generateContentWithType($inputs['type'], [
            'user_name' => $inputs['actor_name'],
            'content' => $inputs['message']
        ]);

        $inputs['data'] = json_encode($inputs['data'], JSON_UNESCAPED_UNICODE);
        // create inbox
        $inputs['inbox_id'] = $inputs['post_id'];
        $inputs['inbox_type'] = 'posts';
        unset($inputs['post_id']);
        unset($inputs['actor_name']);
        unset($inputs['message']);
        $user_inbox = $this->repo_base->create($inputs);
        // send to inbox
        $job = (new ProcessSendNotificationInboxOneTimeJob($user_inbox))
            ->onQueue(QueueMap::QUEUE_USER_INBOX);
        dispatch($job);
        return [
            'code' => '200',
            'data' => $this->formatData($user_inbox)
        ];
    }

    public function sendCommentNotificationToParentComment($inputs){
        $inputs['type'] = UserInbox::TYPE_COMMENT_TO_PARENT_COMMENT;
        if(!isset($inputs['user_id'])) {
            return [ 'code' => '003', 'message' => 'Id user' ];
        }
//        if(!isset($inputs['user_type'])) { $inputs['user_type'] = PolymorphyMap::USER; }
//        if(!isset($inputs['comment_type'])) { $inputs['comment_type'] = UserInbox::COMMENT_TYPE_WITHOUT_COMMENT; }
        if(!isset($inputs['read_status'])) { $inputs['read_status'] = UserInbox::READ_STATUS_NOT_READ; }
        if(!isset($inputs['status'])) { $inputs['status'] = UserInbox::STATUS_NEW; }

        $inputs['title'] = $this->generateTitleWithType($inputs['type'],[
            'user_name' => $inputs['actor_name']
        ]);
        if(!isset($inputs['title'])) {
            return [ 'code' => '008', 'message' => 'Loại thông báo' ];
        }
        $inputs['data'] = [
            'post_id' => $inputs['post_id'],
            'type' => $inputs['type']
        ];

        $inputs['content'] = $this->generateContentWithType($inputs['type'], [
            'user_name' => $inputs['actor_name'],
            'content' => $inputs['message']
        ]);

        $inputs['data'] = json_encode($inputs['data'], JSON_UNESCAPED_UNICODE);
        // create inbox
        $inputs['inbox_id'] = $inputs['comment_id'];
        $inputs['inbox_type'] = 'comments';
        unset($inputs['comment_id']);
        unset($inputs['post_id']);
        unset($inputs['actor_name']);
        unset($inputs['message']);
        $user_inbox = $this->repo_base->create($inputs);
        // send to inbox
        $job = (new ProcessSendNotificationInboxOneTimeJob($user_inbox))
            ->onQueue(QueueMap::QUEUE_USER_INBOX);
        dispatch($job);
        return [
            'code' => '200',
            'data' => $this->formatData($user_inbox)
        ];
    }

    public function sendNotification($inbox) {
        if(in_array($inbox->status, [UserInbox::STATUS_NEW])) {
            $cover = isset($inbox->medias) && count($inbox->medias) > 0 ? $inbox->medias[0]->path : null;
            $data = isset($inbox->data) ? json_decode($inbox->data, true) : [];
            if(isset($cover)){
                $data['cover'] = $cover;
            }
            $this->service_send_notification->sendNotificationByUserIds([$inbox->user_id], $inbox->user_type, [
                'data' => count($data) > 0 ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
                'content' => $inbox->content,
                'title' => $inbox->title
            ]);
            // TODO: send to one signal
            $this->successSendNotification($inbox);
        }
        return $inbox;
    }

    public function sendOneTimeNotification($inbox) {
        if(in_array($inbox->status, [UserInbox::STATUS_NEW])) {
            $cover = isset($inbox->medias) && count($inbox->medias) > 0 ? $inbox->medias[0]->path : null;
            $data = isset($inbox->data) ? json_decode($inbox->data, true) : [];
            if(isset($cover)){
                $data['cover'] = $cover;
            }

            $this->service_send_notification->sendNotificationByUserIds([$inbox->user_id], $inbox->user_type, [
                'data' => count($data) > 0 ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
                'content' => $inbox->content,
                'title' => $inbox->title
            ]);
            // delete after finished
//            $this->repo_base->delete($inbox->id);
        }
    }

    private function successSendNotification($data) {
        $data->attempts += 1;
        $data->status = UserInbox::STATUS_SEND;
        $data->update();
        return $data;
    }

    public function failedSendNotification($data) {
        if($data->attempts < config('enums.notification.retry')) {
            $job = (new ProcessSendNotificationInboxJob($data))
                ->onQueue(QueueMap::QUEUE_USER_INBOX)
                ->delay(Carbon::now()->addMinute(1));
            dispatch($job);
            $data->attempts += 1;
            $data->update();
        } else {
            $data->attempts += 1;
            $data->status = UserInbox::STATUS_SEND_FAILED;
            $data->update();
        }
        return $data;
    }

    private function generateTitleWithType($type, $data = []) {
        switch ($type) {
            case UserInbox::TYPE_COMMENT_TO_POST_OWNER:
                $user_name = '';
                if(isset($data['user_name'])){
                    $user_name = $data['user_name'];
                }
                return sprintf(config('user_inbox.title')[UserInbox::TYPE_COMMENT_TO_POST_OWNER]['vi'], $user_name);
            case UserInbox::TYPE_COMMENT_TO_PARENT_COMMENT:
                $user_name = '';
                if(isset($data['user_name'])){
                    $user_name = $data['user_name'];
                }
                return sprintf(config('user_inbox.title')[UserInbox::TYPE_COMMENT_TO_PARENT_COMMENT]['vi'], $user_name);
        }
        return null;
    }

    private function generateContentWithType($type, $data = []) {
        $user_name = '';
        if(isset($data['user_name'])){
            $user_name = $data['user_name'];
        }
        $content = '';
        if(isset($data['content'])){
            $content = $data['content'];
        }
        switch ($type) {
            case UserInbox::TYPE_COMMENT_TO_POST_OWNER:
                return sprintf(config('user_inbox.content')[UserInbox::TYPE_COMMENT_TO_POST_OWNER]['vi'], $user_name, $content);
            case UserInbox::TYPE_COMMENT_TO_PARENT_COMMENT:
                return sprintf(config('user_inbox.content')[UserInbox::TYPE_COMMENT_TO_PARENT_COMMENT]['vi'], $user_name, $content);
        }
        return null;
    }

    public function checkInputs($inputs, $id)
    {
        if (!isset($inputs['support_id'])) {
            return ['is_failed' => true, 'code' => '003', 'message' => 'Hỗ trợ'];
        }

        if (!isset($inputs['title'])) {
            return ['is_failed' => true, 'code' => '003', 'message' => 'Tiêu đề'];
        }

        if (!isset($inputs['content'])) {
            return ['is_failed' => true, 'code' => '003', 'message' => 'Nội dung'];
        }

        if(!isset($inputs['status'])) {
            $inputs['status'] = UserInbox::STATUS_NEW;
        }
        if(isset($inputs['medias'])){
            $inputs['medias'] = json_encode($inputs['medias'], JSON_UNESCAPED_UNICODE);
        }
        return [
            'is_failed' => false,
            'inputs' => $inputs
        ];
    }

    public function generateColumn($inputs, $columns) {
        if(isset($inputs['status']) && $inputs['status'] != 'all') {
            array_push($columns, $this->getTableName() . '.status = \'' . $inputs['status'] . '\'');
        }
        if(isset($inputs['type']) && $inputs['type'] != 'all') {
            array_push($columns, $this->getTableName() . '.type = \'' . $inputs['type'] . '\'');
        }
        if(isset($inputs['support_id']) && $inputs['support_id'] != 'all') {
            array_push($columns, $this->getTableName() . '.support_id = \'' . $inputs['support_id'] . '\'');
        }
        if(isset($inputs['user_id']) && $inputs['user_id'] != 'all') {
            array_push($columns, $this->getTableName() . '.user_id = \'' . $inputs['user_id'] . '\'');
        }
        if(isset($inputs['user_type']) && $inputs['user_type'] != 'all') {
            array_push($columns, $this->getTableName() . '.user_type = \'' . $inputs['user_type'] . '\'');
        }
        if(isset($inputs['read_status']) && $inputs['read_status'] != 'all') {
            array_push($columns, $this->getTableName() . '.read_status = \'' . $inputs['read_status'] . '\'');
        }
        return $columns;
    }

    public function formatData($data)
    {
        $res = parent::formatData($data);
        if(isset($res['data'])) {
            $res['data'] = json_decode($res['data'], true);
        }
        return $res;
    }

    public function getQueryDateField() {
        return [
            $this->getTableName() .'.created_at',
            $this->getTableName() .'.updated_at'
        ];
    }

    public function getQueryField() {
        return [
            $this->getTableName() .'.id',
            $this->getTableName() .'.user_id',
            $this->getTableName() .'.user_type',
            $this->getTableName() .'.title',
            $this->getTableName() .'.content',
            $this->getTableName() .'.data',
            $this->getTableName() .'.type',
            $this->getTableName() .'.comment_type',
            $this->getTableName() .'.status',
            $this->getTableName() .'.read_status',
        ];
    }

//    private function getAuthInputs($inputs) {
//        $auth = Auth::guard('users')->user();
//        // auth by user
//        if(isset($auth)) {
//            $inputs['user_id'] = $auth->id;
//            $inputs['user_type'] = PolymorphyMap::USER;
//        } else {
//            $auth = Auth::guard('employees')->user();
//            if(isset($auth)) {
//                $inputs['user_id'] = $auth->id;
//                $inputs['user_type'] = PolymorphyMap::EMPLOYEE;
//            }
//        }
//        return $inputs;
//    }
}
