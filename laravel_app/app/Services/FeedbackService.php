<?php

namespace App\Services;

use App\Models\Feedback;
use App\Repositories\Interfaces\FeedbackRepositoryInterface;
use App\Services\Base\BaseService;

class FeedbackService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        FeedbackRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Feedback';
    }

    public function getTableName()
    {
        return (new Feedback())->getTable();
    }
}
