<?php

namespace App\Services;

use App\Models\Address;
use App\Repositories\AddressRepository;
use App\Repositories\Interfaces\AddressRepositoryInterface;
use App\Services\Base\BaseService;

class AddressService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        AddressRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Address';
    }

    public function getTableName()
    {
        return (new Address())->getTable();
    }


}
