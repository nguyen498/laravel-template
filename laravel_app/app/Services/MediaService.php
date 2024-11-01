<?php

namespace App\Services;

use App\Models\Media;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Services\Base\BaseService;

class MediaService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        MediaRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Media';
    }

    public function getTableName()
    {
        return (new Media())->getTable();
    }

}
