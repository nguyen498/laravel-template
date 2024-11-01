<?php

namespace App\Repositories\Interfaces;

interface DeviceRepositoryInterface extends BaseRepositoryInterface
{
    public function loginBy($token, $inputs, $id, $type);
    public function removeTokenExist($token, $type);
    public function logoutBy($token, $id, $type);
    public function logoutAllBy($id, $type);
    public function findTokenByAbleIds($user_ids, $type);
}
