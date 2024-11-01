<?php

namespace App\Repositories;

use App\Models\Device;
use App\Repositories\Interfaces\DeviceRepositoryInterface;

class DeviceRepository extends BaseRepository implements DeviceRepositoryInterface
{
    public function getModel()
    {
        return Device::class;
    }

    public function loginBy($token, $device_info, $id, $type)
    {
        $this->removeTokenExist($token, $type);

        $datas = [
            'push_token' => $token,
            'deviceable_id' => $id,
            'deviceable_type' => $type
        ];
        if(isset($device_info)) {
            $datas['device_info'] = json_encode($device_info, JSON_UNESCAPED_UNICODE);
            // set from device info
            if(isset($device_info['platform']) && !empty($device_info['platform'])) {
                $datas['platform'] = $device_info['platform'];
            }
        }
        return $this->create($datas);
    }

    public function removeTokenExist($token, $type)
    {
        $this->model
            ->where('push_token', $token)
            ->where('deviceable_type', $type)
            ->delete();
    }

    public function logoutBy($token, $id, $type)
    {
        $this->model->where([
            'push_token' => $token,
            'deviceable_id' => $id,
            'deviceable_type' => $type
        ])->delete();
        return true;
    }

    public function findTokenByAbleIds($able_ids, $type)
    {
        $datas = $this->model->whereNotNull('push_token')
            ->whereIn('deviceable_id', $able_ids)
            ->where('deviceable_type', $type)
            ->whereRaw('CHAR_LENGTH(push_token) = 36')
            ->get(['push_token']);
        $tokens = [];
        if(count($datas) > 0){
            foreach($datas as $data){
                array_push($tokens, $data->push_token);
            }
        }
        return $tokens;
    }

    public function logoutAllBy($id, $type)
    {
        $this->model->where([
            'deviceable_id' => $id,
            'deviceable_type' => $type
        ])->delete();
        return true;
    }
}
