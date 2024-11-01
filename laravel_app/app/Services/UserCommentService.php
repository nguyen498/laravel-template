<?php

namespace App\Services;

use App\Models\UserComment;
use App\Repositories\Interfaces\UserCommentRepositoryInterface;
use App\Services\Base\BaseService;

class UserCommentService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        UserCommentRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'User comment';
    }

    public function getTableName()
    {
        return (new UserComment())->getTable();
    }
}
