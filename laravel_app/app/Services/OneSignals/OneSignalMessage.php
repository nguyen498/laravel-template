<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 4/25/2019
 * Time: 1:20 PM
 */

namespace App\Services\OneSignals;


class OneSignalMessage
{
    /**
     * @param string $titlez
     * @param string $messages
     * @param array $ids
     * @param $data
     * @return array
     */
    public static function sendUser(string $title, string $messages, array $ids, $data) {
        $result = [
            'title' => [
                'en' => $title,
                'vi' => $title
            ],
            'message' => [
                'en' => $messages,
                'vi' => $messages
            ],
            'ids' => $ids
        ];
        if(isset($data)){
            $result += [ 'data' => $data ];
            // set cover if exist
            if(isset($data['cover'])){
                $result += [ 'image_url' => $data['cover'] ];
            }
        }
        return $result;
    }

    /**
     * @param string $title
     * @param string $messages
     * @param $data
     * @return array
     */
    public static function sendAll(string $title, string $messages, $data) {
        $result =  [
            'title' => [
                'en' => $title,
                'vi' => $title
            ],
            'message' => [
                'en' => $messages,
                'vi' => $messages
            ]
        ];
        if(isset($data)){
            $result += [ 'data' => $data ];
            // set cover if exist
            if(isset($data['cover'])){
                $result += [ 'image_url' => $data['cover'] ];
            }
        }
        return $result;
    }

    /**
     * @param $user
     * @return array
     */
    public static function getDeviceToken($user){
        $devices = [];
        foreach($user->devices as $device){
            if(isset($device->push_token)){
                array_push($devices, $device->push_token);
            }

        }
        return $devices;
    }

    public static function getDeviceTokens($uses){
        $devices = [];
        foreach($uses as $user){
            foreach($user->devices as $device){
                if(isset($device->push_token)){
                    array_push($devices, $device->push_token);
                }
            }
        }
        return $devices;
    }
}
