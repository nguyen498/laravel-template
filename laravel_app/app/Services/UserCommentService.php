<?php


namespace App\Services;


use App\Constants\PolymorphyMap;
use App\Models\Support;
use App\Models\UserComment;
use App\Repositories\Interfaces\SupportRepositoryInterface;
use App\Repositories\Interfaces\UserCommentRepositoryInterface;
use App\Services\Base\BaseService;
use App\Services\Client\TripService;

class UserCommentService extends BaseService
{
    protected $repo_base;
    protected $repo_supports;
    protected $service_user_inbox;
    protected $service_trip;
    protected $service_telegram;
    protected $with;

    public function __construct(
        UserCommentRepositoryInterface $repo_base,
        SupportRepositoryInterface $repo_supports,
        UserInboxService $service_user_inbox,
        TripService $service_trip,
        TelegramMessageService $service_telegram
    ) {
        $this->repo_base                = $repo_base;
        $this->repo_supports            = $repo_supports;
        $this->service_user_inbox       = $service_user_inbox;
        $this->service_trip             = $service_trip;
        $this->service_telegram         = $service_telegram;
        $this->with                     = [];
    }

    public function getModelName()
    {
        return 'Hộp thư';
    }

    public function sendMessage($inputs) {
        $inputs = $this->getAuthInputs($inputs);
        $check =  $this->checkInputs($inputs, null);
        if($check['is_failed']) {
            return $check;
        }
        $inputs = $check['inputs'];
        $data = $this->repo_base->create($inputs);
        $data = $this->repo_base->findById($data->id);
        // auto update support
        $this->updateSupport($data);
        // send notificaiton
        $this->sendOneTimeNotification($data);
        return [
            'code' => '200',
            'data' => $this->formatData($data)
        ];
    }

    public function updateIsRead($inputs) {
        $inputs = $this->getAuthInputs($inputs);
        if(!isset($inputs['id'])) {
            return [ 'code' => '003', 'message' => 'Định danh tin nhắn' ];
        }
        if(!isset($inputs['object_id'])) {
            return [ 'code' => '003', 'message' => 'Định danh phòng chat' ];
        }
        if(!isset($inputs['object_type'])) {
            return [ 'code' => '003', 'message' => 'Loại phòng chat' ];
        }
        $this->repo_base->updateIsRead([
            'id' => $inputs['id'],
            'object_id' => $inputs['object_id'],
            'object_type' => $inputs['object_type'],
        ], $inputs['actor_id'], $inputs['actor_type']);

        return [
            'code' => '200',
            'message' => 'update is read'
        ];
    }

    public function countIsRead($inputs) {
        $inputs = $this->getAuthInputs($inputs);
        if(!isset($inputs['object_id'])) {
            return [ 'code' => '003', 'message' => 'Định danh phòng chat' ];
        }
        if(!isset($inputs['object_type'])) {
            return [ 'code' => '003', 'message' => 'Loại phòng chat' ];
        }
        $count = $this->repo_base->countIsRead([
            'is_read' => UserComment::NOT_READ,
            'object_id' => $inputs['object_id'],
            'object_type' => $inputs['object_type'],
        ], $inputs['actor_id'], $inputs['actor_type']);

        return [
            'code' => '200',
            'data' => [
                'is_read' => $count > 0 ? true : false,
                'total' => $count
            ]
        ];
    }

    public function searchApp($inputs) {
        $inputs = $this->getAuthInputs($inputs);
        if(!isset($inputs['actor_id'])) {
            return [ 'code' => '401', 'message' => '' ];
        }
        if(isset($inputs['filter']['object_type'])){
            if(isset($inputs['filter']['object_id'])) {
                if($inputs['filter']['object_type'] == 'trips') {
                    $res_trip = $this->service_trip->findOnlyTripById($inputs['filter']['object_id']);
                    if(isset($res_trip['data'])) {
                        $trip = $res_trip['data'];
                        // check from customer
                        if($inputs['actor_id'] != $trip['driver_id']
                            && $inputs['actor_id'] != $trip['passenger_id']){
                            return [
                                'code' => '200',
                                'data' => [ 'data' => [], 'total' => 0 ]
                            ];
                        }
                    }
                }
            }

        }
        return $this->search($inputs);
    }

    public function search($inputs)
    {
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;
        $text = null;
        $columns = [];
        $columnsHas = [];
        $term = isset($inputs['term']) ? $inputs['term'] : [];
        $with = isset($inputs['with']) ? $inputs['with'] : $this->with;
        $page = isset($inputs['page']) ? $inputs['page'] : 1;
        $limit = isset($inputs['limit']) ? $inputs['limit'] : 30;
        $orderBy = isset($inputs['order_by']) ? $inputs['order_by'] : 'created_at';
        $sort = isset($inputs['sort']) ? $inputs['sort'] : 'desc';
        $joins = $this->getJoinTable();

        $orderBy = $this->generateOrder($orderBy);
        $select = $this->generateSelect($inputs, $this->getTableName());

        if(!isset($inputs['filter']['object_id']) || !isset($inputs['filter']['object_type'])){
            return [
                'code' => '200',
                'data' => [ 'data' => [], 'total' => 0 ]
            ];
        }
        $columns = $this->generateColumn($inputs['filter'], $columns);
        // generate conditions from term
        $query = $this->generateQuery($term, $columns);
        $columns = $query['columns'];
        $text = $query['search'];

        $datas = $this->repo_base->searchText($text, $columns, $columnsHas, $joins, $page, $limit, $orderBy, $sort, $with, $select);
        $count = $this->repo_base->searchTextCount($text, $columns, $columnsHas, $joins);

        return [
            'code' => '200',
            'data' => [
                'data' => $this->formatSelectData($datas),
                'total' => $count
            ]
        ];
    }

    public function destroyApp($inputs)
    {
        $inputs = $this->getAuthInputs($inputs);
        if (!isset($inputs['actor_id'])) {
            return ['code' => '401', 'message' => ''];
        }
        $comment = $this->repo_base->findById($inputs['id']);
        if(!isset($comment)){
            return ['code' => '004', 'message' => 'Tin nhắn chat'];
        }
        if($comment->actor_id != $inputs['actor_id']
            && $comment->actor_type != $inputs['actor_type']) {
            return ['code' => '008', 'message' => 'Tin nhắn chat'];
        }
        $this->repo_base->delete($comment->id);

        return [
            'code' => '200', 'message' => 'Xóa thành công'
        ];

    }

    public function getTableName()
    {
        return (new UserComment())->getTable();
    }

    public function checkInputs($inputs, $id)
    {
        if (!isset($inputs['actor_id'])) {
            return ['is_failed' => true, 'code' => '003', 'message' => 'Người chat'];
        }

        if (!isset($inputs['object_id'])) {
            return ['is_failed' => true, 'code' => '003', 'message' => 'Phòng chat'];
        }

        if (!isset($inputs['message'])) {
            return ['is_failed' => true, 'code' => '003', 'message' => 'Nội dung'];
        }

        if(!isset($id) && !isset($inputs['verb'])) {
            $inputs['verb'] = PolymorphyMap::CHAT;
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
        if(isset($inputs['actor_id']) && $inputs['actor_id'] != 'all') {
            array_push($columns, $this->getTableName() . '.actor_id = \'' . $inputs['actor_id'] . '\'');
        }
        if(isset($inputs['actor_type']) && $inputs['actor_type'] != 'all') {
            array_push($columns, $this->getTableName() . '.actor_type = \'' . $inputs['actor_type'] . '\'');
        }
        if(isset($inputs['object_id']) && $inputs['object_id'] != 'all') {
            array_push($columns, $this->getTableName() . '.object_id = \'' . $inputs['object_id'] . '\'');
        }
        if(isset($inputs['object_type']) && $inputs['object_type'] != 'all') {
            array_push($columns, $this->getTableName() . '.object_type = \'' . $inputs['object_type'] . '\'');
        }
        return $columns;
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
            $this->getTableName() .'.parent_id',
            $this->getTableName() .'.actor_id',
            $this->getTableName() .'.actor_type',
            $this->getTableName() .'.actor_name',
            $this->getTableName() .'.message',
            $this->getTableName() .'.verb',
            $this->getTableName() .'.object_id',
            $this->getTableName() .'.object_type',
        ];
    }

    private function getAuthInputs($inputs) {
        if(isset($inputs['auth'])) {
            $auth = $inputs['auth'];
            if(isset($auth['type'])) {
                switch ($auth['type']) {
                    case 'passengers':
                        $inputs['actor_id'] = $auth['passenger_id'];
                        $inputs['actor_type'] = PolymorphyMap::USER;
                        break;
                    case 'drivers':
                        $inputs['actor_id'] = $auth['driver_id'];
                        $inputs['actor_type'] = PolymorphyMap::DRIVER;
                        break;
                    case 'employees':
                        $inputs['actor_id'] = $auth['employee_id'];
                        $inputs['actor_type'] = PolymorphyMap::EMPLOYEE;
                        break;
                }
            }
            if(isset($auth['phone'])) {
                $inputs['actor_name'] = $auth['phone'];
            }
            if(isset($auth['name'])) {
                $inputs['actor_name'] = $auth['name'];
            }
        }
        return $inputs;
    }

    public function updateSupport($comment) {
        if($comment->object_type === PolymorphyMap::SUPPORT) {
            $support = $this->repo_supports->findById($comment->object_id);
            if(isset($support)) {
                $inputs = [
                    'status' => Support::STATUS_ADMIN_REPLY,
                    'is_read_admin' => false,
                    'is_read_user' => true
                ];
                if($comment->actor_type === PolymorphyMap::DRIVER
                    || $comment->actor_type === PolymorphyMap::USER) {
                    $inputs['status'] = Support::STATUS_USER_REPLY;
                    $inputs['is_read_admin'] = true;
                    $inputs['is_read_user'] = false;

                }
                $this->repo_supports->update($support->id, $inputs);

                if($comment->actor_type === PolymorphyMap::EMPLOYEE) {
                    $input_chat = [
                        'support_id' => $support->id,
                        'support_reference' => $support->reference,
                        'support_title' => $support->title
                    ];
                    if($support->type === Support::TYPE_PASSENGER) {
                        $input_chat['user_id'] = $support->passenger_id;
                        $input_chat['user_type'] = PolymorphyMap::USER;
                    }

                    $this->service_user_inbox->sendChatSupport($input_chat);
                } else {
                    // send telegram message if it send by user or driver
                    $this->sendTelegramMessage($support);
                }
            }
        }
    }

    public function sendOneTimeNotification($comment) {
        if($comment->object_type === PolymorphyMap::TRIP) {
            $res_trip = $this->service_trip->findOnlyTripById($comment->object_id);
            if(isset($res_trip['data'])) {
                $trip = $res_trip['data'];
                $inputs = [];
                if($comment->actor_type === PolymorphyMap::USER) {
                    $inputs['user_id'] = $trip['driver_id'];
                    $inputs['user_type'] = PolymorphyMap::DRIVER;
                } else {
                    $inputs['user_id'] = $trip['passenger_id'];
                    $inputs['user_type'] = PolymorphyMap::USER;
                }
                $inputs['trip_id'] = $trip['id'];
                $inputs['trip_reference'] = $trip['reference'];
                $inputs['driver_name'] = $trip['driver_name'];
                $inputs['driver_phone'] = $trip['driver_phone'];
                $inputs['passenger_name'] = $trip['passenger_name'];
                $inputs['passenger_phone'] = $trip['passenger_phone'];

                $this->service_user_inbox->sendChatTrip($inputs);
            }

        }
    }

    private function setSupportCommentContent($data) {
        $content = null;
        if($data->type === Support::TYPE_PASSENGER) {
            $content = sprintf(config('telegram_content.support.passenger'),
                $data->user_reference,
                $data->phone,
                $data->title
//                config('telegram_content.support.user_comment')
            );
        } else if($data->type === Support::TYPE_DRIVER) {
            $content = sprintf(config('telegram_content.support.driver'),
                $data->user_reference,
                $data->phone,
                $data->title
//                config('telegram_content.support.user_comment')
            );
        }
        return $content;
    }
}
