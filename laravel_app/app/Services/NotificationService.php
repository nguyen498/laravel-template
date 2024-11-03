<?php


namespace App\Services;


use App\Constants\PolymorphyMap;
use App\Constants\QueueMap;
use App\Jobs\ProcessSendNotificationJob;
use App\Models\Notification;
use App\Models\UserInbox;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\UserInboxRepositoryInterface;
use App\Services\Base\BaseService;
use App\Utils\LogHelper;
use Carbon\Carbon;
use DateTimeZone;

class NotificationService extends BaseService
{
    protected $repo_base;
    protected $repo_user_inbox;
    protected $service_send_notification;
    protected $with;

    public function __construct(
        NotificationRepositoryInterface $repo_base,
        UserInboxRepositoryInterface $repo_user_inbox,
        SendNotificationService $service_send_notification
    ) {
        $this->repo_base                = $repo_base;
        $this->repo_user_inbox          = $repo_user_inbox;
        $this->service_send_notification = $service_send_notification;
        $this->with                     = [];
    }

    public function getModelName()
    {
        return 'Thông báo';
    }

    public function store($inputs)
    {
        $now = Carbon::now()->subMinutes(5);
        if(!isset($inputs['type'])) {
            $inputs['type'] = Notification::TYPE_USER;
        }
        if(($inputs['send_type'] == Notification::TYPE_EVERYDAY || $inputs['send_type']== Notification::TYPE_EVERY_WEEK || $inputs['send_type']== Notification::TYPE_EVERY_MONTH))
        {
            if (!isset($inputs['send_date'])) {
                return ['code' => '003', 'message' => 'Thời gian gửi'];
            }
        }

        $inputs['reference'] = $this->repo_base->getReferenceByPrefix(config('enums.notification.prefix'), 'reference', config('enums.code_length.notification'));
        if(isset($inputs['medias'])) {
            $inputs['medias'] = json_encode($inputs['medias'], JSON_UNESCAPED_UNICODE);
        }
        // set input data for notification by type
        $input_data = $this->setInputData($inputs);
        if($input_data['is_failed']) {
            return $input_data;
        }
        $inputs = $input_data['inputs'];

        $data = $this->repo_base->create($inputs);
        $notification = $this->repo_base->findById($data->id, $this->with);

        return [
            'code' => '200',
            'data' => $this->formatData($notification)
        ];
    }

    public function update($id, $inputs)
    {
        $data = $this->repo_base->findById($id);
        if(!isset($data)) { return [ 'code' => '004', 'message' => 'Thông báo']; }

        if(!isset($inputs['type'])) {
            $inputs['type'] = Notification::TYPE_USER;
        }
        if(isset($inputs['medias'])) {
            $inputs['medias'] = json_encode($inputs['medias'], JSON_UNESCAPED_UNICODE);
        }
        if(($inputs['send_type'] == Notification::TYPE_EVERYDAY || $inputs['send_type']== Notification::TYPE_EVERY_WEEK || $inputs['send_type']== Notification::TYPE_EVERY_MONTH))
        {
            if (!isset($inputs['send_date'])) {
                return ['code' => '003', 'message' => 'Thời gian gửi'];
            }
        }
        // set input data for notification by type
        $input_data = $this->setInputData($inputs);
        if($input_data['is_failed']) {
            return $input_data;
        }
        $inputs = $input_data['inputs'];
        if(!isset($inputs['user_ids'])) { $inputs['user_ids'] = null; }

        $inputs['last_run'] = null;
        $data->update($inputs);
        $data = $this->repo_base->findById($data->id, $this->with);

        $notification = $this->repo_base->findById($data->id, $this->with);
//        $this->processSendSystemNotification();
        return [
            'code' => '200',
            'data' => $this->formatData($notification)
        ];
    }

    private function setInputData($inputs) {
        if($inputs['type'] === Notification::TYPE_USER){
            $res = $this->getUserIds();
            if($res['is_failed']) { return $res; }
            if(count($res['data']) > 0) {
                $inputs['user_ids'] = implode(',', $res['data']);
            }
        }

        return [
            'is_failed' => false,
            'inputs' => $inputs
        ];
    }

    public function sendNotificationById($id) {
        $data = $this->repo_base->findById($id, $this->with);
        if(isset($data)) {
            // sleep for 0.3s
            usleep(300000);
            if($data->send_type == Notification::TYPE_ONCE &&
                $data->send_date == null && $data->status != Notification::STATUS_DRAFF){
                $this->repo_base->update($data->id, ['status' => Notification::STATUS_SEND]);

                $this->sendSystemNotification($data->id);
            }
        }
    }

    public function processSendSystemNotification(){
        $array = [];
        $timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
        $to = Carbon::now()->setTimezone($timezone)->format('Y-m-d H:i:59');
        $from= Carbon::now()->setTimezone($timezone)->subMinutes(4)->format('Y-m-d H:i:00');
        $data_once = $this->repo_base->findNotificationByWhere(Notification::STATUS_READY, $from, $to);
        if(count($data_once) > 0){
            foreach ($data_once as $data){
                $this->repo_base->update($data->id, ['status' => Notification::STATUS_SEND]);
                $this->sendSystemNotification($data->id, true);
            }
            $array['data_once'] = $this->repo_base->findNotificationByWhere(Notification::STATUS_SEND, $from, $to);
        }
        $data_day = $this->repo_base->findNotificationDay();
        if(count($data_day) > 0){
            $array['data_day'] = $data_day;
            foreach ($data_day as $data){
                $now = Carbon::now()->setTimezone($timezone);
                $last_run = Carbon::parse($data->last_run);
                if($data->last_run === null || ($last_run->toDateString() < $now->toDateString())) {
                    $data->update([
                        'last_run' => $now->toDateTimeString()
                    ]);
                    $this->sendSystemNotification($data->id, true);
                }
            }
        }
        $data_week = $this->repo_base->findNotificationWeek();
        if(count($data_week) > 0){
            $array['data_week'] = $data_week;
            foreach ($data_week as $data){
                $now = Carbon::now()->setTimezone($timezone);
                $last_run = Carbon::parse($data->last_run);
                if($data->last_run === null || ($last_run->toDateString() < $now->toDateString())){
                    $data->update([
                        'last_run' => $now->toDateTimeString()
                    ]);
                    $this->sendSystemNotification($data->id, true);
                }
            }
        }
        $data_month = $this->repo_base->findNotificationMonth();
        if(count($data_month) > 0){
            $array['data_month'] = $data_month;
            foreach ($data_month as $data){
                $now = Carbon::now()->setTimezone($timezone);
                $last_run = Carbon::parse($data->last_run);
                if($data->last_run === null || ($last_run->toDateString() < $now->toDateString())) {
                    $data->update([
                        'last_run' => $now->toDateTimeString()
                    ]);
                    $this->sendSystemNotification($data->id, true);
                }
            }
        }
        return $array;
    }

    public function sendSystemNotification($id, $is_get_user_ids = false){
        $data = $this->repo_base->findById($id, []);
        if(isset($data)){
            if($data->type == Notification::TYPE_USER){
                $users = explode(',', $data->user_ids);
                if($is_get_user_ids) {
                    $res = $this->getUserIds();
                    if($res['is_failed']) { $users = []; }
                    else {
                        $users = $res['data'];
                    }
                }
                if(isset($users) && count($users) > 0) {
                    $insert_sqls = [];
                    foreach ($users as $key => $user){
                        array_push($insert_sqls, $this->repo_user_inbox->createNotificationSql($data, $user, PolymorphyMap::USER));
                    }
                    if(count($insert_sqls) > 0) {
                        $this->repo_user_inbox->insertDBs($insert_sqls);
                    }
                }
            }
            else if($data->type == Notification::TYPE_ALL){
                $users = explode(',', $data->user_ids);
                if($is_get_user_ids) {
                    $res = $this->getUserIds();
                    if($res['is_failed']) { $users = []; }
                    else {
                        $users = $res['data'];
                    }
                }
                if(isset($users) && count($users) > 0) {
                    $insert_sqls = [];
                    foreach ($users as $key => $user){
                        array_push($insert_sqls, $this->repo_user_inbox->createNotificationSql($data, $user, PolymorphyMap::USER));
                    }
                    if(count($insert_sqls) > 0) {
                        $this->repo_user_inbox->insertDBs($insert_sqls);
                    }
                }
            }
            $job = (new ProcessSendNotificationJob($data))
                ->onQueue(QueueMap::QUEUE_NOTIFICATION);
            dispatch($job);
        }
        return $data;
    }

    public function processSendUserNotification($notification)
    {
        try {
            $this->sendUserNotification($notification);
            $timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
            // update after fininshed if send_type is once
//            $this->repo_base->update($notification->id, [
//                'send_status' => Notification::SEND_SUCCESS,
//                'last_run' => Carbon::now()->setTimezone($timezone)->toDateTimeString()
//            ]);
        } catch(\Exception $e) {
            LogHelper::writeLog('issued on send notification ' . $e->getTrace(), 0);
        }
    }

    public function processSendUserOneTimeNotification($notification) {
        try {
            $this->sendUserNotification($notification);
            // delete after success
            $this->repo_base->delete($notification->id);
        } catch(\Exception $e) {
            LogHelper::writeLog('issued on send notification ' . $e->getTrace(), 0);
        }
    }

    private function sendUserNotification($notification) {
        $cover = isset($notification->medias) && count($notification->medias) > 0 ? $notification->medias[0]->path : null;
        $data = isset($notification->data) ? json_decode($notification->data, true) : [];
        if(isset($cover)){
            $data['cover'] = $cover;
        }

        $user_ids = $notification->user_ids;
        if(isset($user_ids) && !empty($user_ids)) {
            $user_ids = explode(',', $user_ids);
            if(count($user_ids) > 0) {
                foreach (array_chunk($user_ids, 1000) as $t) {
                    $this->service_send_notification->sendNotificationByUserIds($t, PolymorphyMap::USER, [
                        'data' => count($data) > 0 ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
                        'content' => $notification->content,
                        'title' => $notification->title
                    ]);
                }
            }
        }
    }

    public function failedSendNotification($notification) {
        if($notification->attempts <= config('enums.notification.retry')) {
            $now = Carbon::now();
            $notification->attempts ++;
            $notification->update();
            $job = (new ProcessSendNotificationJob($notification))
                ->onQueue(QueueMap::QUEUE_NOTIFICATION)
                ->delay($now->addMinutes(1));
            dispatch($job);
        } else {
            $notification->update([
                'send_status' => Notification::SEND_DESTROY
            ]);
        }
    }

    public function generateOrder($orderBy){
        if($orderBy == 'status_name'){
            $orderBy = $this->getTableName() . '.status';
        }
        if($orderBy == 'type_name'){
            $orderBy = $this->getTableName() . '.type';
        }
        if($orderBy == 'send_type_name'){
            $orderBy = $this->getTableName() . '.send_type';
        }
        return $orderBy;
    }

    public function generateColumn($inputs, $columns)
    {
        if (!isset($inputs['display_type'])) {
            array_push($columns, $this->getTableName() . '.display_type = 1');
        } else if (isset($inputs['display_type']) && strtolower($inputs['display_type']) != 'all') {
            array_push($columns, $this->getTableName() . '.display_type = \'' . $inputs['display_type'] . '\'');
        }
        if (isset($inputs['status']) && strtolower($inputs['status']) != 'all') {
            array_push($columns, $this->getTableName() . '.status = \'' . $inputs['status'] . '\'');
        }
        if (isset($inputs['type']) && strtolower($inputs['type']) != 'all') {
            array_push($columns, $this->getTableName() . '.type = \'' . $inputs['type'] . '\'');
        }
        if (isset($inputs['send_type']) && strtolower($inputs['send_type']) != 'all') {
            array_push($columns, $this->getTableName() . '.send_type = \'' . $inputs['send_type'] . '\'');
        }
        return $columns;
    }

    public function formatData($data)
    {
        $res = parent::formatData($data);
        if (isset($data->status)) {
            $res['status_name'] = config('enums.notification.status')[$data->status];
        }
        if (isset($data->type)) {
            $res['type_name'] = config('enums.notification.type')[$data->type];
        }
        if (isset($data->send_type)) {
            $res['send_type_name'] = config('enums.notification.send_type')[$data->send_type];
        }
        return $res;
    }

    private function getUserIds($datas) {
        $user_ids = [];
        foreach($datas as $data) {
            $user_ids[] = $data['id'];
        }
        return [
            'is_failed' => false,
            'data' => $user_ids
        ];
    }

    public function getTableName()
    {
        return (new Notification())->getTable();
    }

    public function getQueryDateField() {
        return [
            $this->getTableName() .'.created_at',
            $this->getTableName() .'.updated_at',
            $this->getTableName() .'.send_date'
        ];
    }

    public function getQueryField() {
        return [
            $this->getTableName() .'.title',
            $this->getTableName() .'.content',
            $this->getTableName() .'.type',
            $this->getTableName() .'.status',
            $this->getTableName() .'.send_type'
        ];
    }
}
