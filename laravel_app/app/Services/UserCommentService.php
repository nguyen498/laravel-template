<?php


namespace App\Services;


use App\Constants\PolymorphyMap;
use App\Models\Post;
use App\Models\User;
use App\Models\UserComment;
use App\Repositories\Interfaces\UserCommentRepositoryInterface;
use App\Services\Base\BaseService;
use Illuminate\Support\Facades\Auth;

class UserCommentService extends BaseService
{
    protected $repo_base;
    protected $service_user_inbox;
    protected $with;

    public function __construct(
        UserCommentRepositoryInterface $repo_base,
        UserInboxService $service_user_inbox,
    ) {
        $this->repo_base                = $repo_base;
        $this->service_user_inbox       = $service_user_inbox;
        $this->with                     = ['actor', 'children'];
    }

    public function getModelName()
    {
        return 'Comment';
    }

    public function getTableName()
    {
        return (new UserComment())->getTable();
    }

    public function sendCommentPost($inputs) {
        $user = Auth::user();
        $check = $this->checkInputsPost($inputs, null);
        if($check['is_failed']) {
            return $check;
        }
        $inputs = $check['inputs'];
        $inputs['actor_id'] = $user->id;
        $inputs['actor_type'] = 'users';
        $inputs['actor_name'] = $user->first_name ?? $user->phone;
        $data = $this->repo_base->create($inputs);
        $data = $this->repo_base->findById($data->id, ['post', 'parentComment']);

        $resp = $this->formatData($data);
        $message = $data->message;
        if((!isset($data->message) || $data->message === "") && isset($data->medias)){
            $medias = json_decode($data->medias);
            $total_medias = count($medias);
            $message = "Bằng {$total_medias} hình ảnh";
        }

        $this->service_user_inbox->sendCommentNotificationToPostOwner([
            'user_id' => $data->post->user_id,
            'post_id' => $data->object_id,
            'actor_name' => $data->actor_name,
            'message' => $message
        ]);
        if(isset($data->parent_id)){
            $this->service_user_inbox->sendCommentNotificationToParentComment([
                'comment_id' => $data->id,
                'user_id' => $data->parentComment->actor_id,
                'post_id' => $data->object_id,
                'actor_name' => $data->actor_name,
                'message' => $message
            ]);
        }

        return [
            'code' => '200',
            'data' => $resp
        ];
    }

    public function updateComment($id,$inputs){
        $data = $this->repo_base->findById($id);
        if (!isset($data)) {
            return ['code' => '004', 'message' => $this->getModelName()];
        }
        $validate = $this->checkInputs($inputs, $id);
        if ($validate['is_failed']) {
            return $validate;
        }
        $input_dat = $validate['inputs'];

        $this->repo_base->update($id, $input_dat);
        $data = $this->repo_base->findById($data->id, $this->with);
        return [
            'code' => '200',
            'data' => $this->formatData($data)
        ];
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
        if (!isset($inputs['actor_id'])) {
            return ['code' => '401', 'message' => ''];
        }
        $comment = $this->repo_base->findById($inputs['id']);
        if(!isset($comment)){
            return ['code' => '004', 'message' => 'Comment'];
        }
        if($comment->actor_id != $inputs['actor_id']
            && $comment->actor_type != $inputs['actor_type']) {
            return ['code' => '008', 'message' => 'Comment'];
        }
        $this->repo_base->delete($comment->id);

        return [
            'code' => '200', 'message' => 'Xóa thành công'
        ];

    }

    public function checkInputs($inputs, $id)
    {
        if (!isset($inputs['message'])) {
            return ['is_failed' => true, 'code' => '003', 'message' => 'Message'];
        }

        return [
            'is_failed' => false,
            'inputs' => $inputs
        ];
    }

    public function checkInputsPost($inputs, $id)
    {
        if (!isset($inputs['post_id'])) {
            return ['is_failed' => true, 'code' => '003', 'message' => 'Post'];
        }

        if (!isset($inputs['message'])) {
            return ['is_failed' => true, 'code' => '003', 'message' => 'Message'];
        }

        if(!isset($id) && !isset($inputs['verb'])) {
            $inputs['verb'] = PolymorphyMap::COMMENT;
        }

        if(isset($inputs['medias'])){
            $inputs['medias'] = json_encode($inputs['medias'], JSON_UNESCAPED_UNICODE);
        }
        $inputs['object_id'] = $inputs['post_id'];
        $inputs['object_type'] = 'posts';
        unset($inputs['post_id']);
        return [
            'is_failed' => false,
            'inputs' => $inputs
        ];
    }

    public function generateColumn($inputs, $columns) {
        array_push($columns, $this->getTableName() . '.parent_id IS NULL');
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
}
