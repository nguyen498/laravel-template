<?php

namespace App\Services;

use App\Models\State;
use App\Repositories\Interfaces\StateRepositoryInterface;
use App\Services\Base\BaseService;

class StateService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        StateRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'State';
    }

    public function getTableName()
    {
        return (new State())->getTable();
    }

    public function generateColumn($inputs, $columns)
    {
        if(isset($inputs['country_id']) && isset($inputs['country_id']) !== 'all'){
            array_push($columns, "country_id = {$inputs['country_id']}");
        }
        return $columns;
    }
}
