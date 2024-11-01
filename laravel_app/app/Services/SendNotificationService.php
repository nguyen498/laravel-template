<?php


namespace App\Services;


use App\Repositories\Interfaces\DeviceRepositoryInterface;
use App\Services\OneSignals\OneSignalMessage;
use App\Services\OneSignals\OneSignalServices;

class SendNotificationService
{
    protected $repo_device;
    protected $app_id;
    protected $rest_api_key;

    public function __construct(
        DeviceRepositoryInterface $repo_device
    )
    {
        $this->repo_device                  = $repo_device;
        $this->app_id                       = config('constants.one_signal.app_id');
        $this->rest_api_key                 = config('constants.one_signal.rest_api_key');
    }

    public function sendNotificationByUserIds($user_ids, $type, $inputs) {
        if(config('constants.send_notification') == 1){
//            $this->setAppKey();

            $tokens = $this->repo_device->findTokenByAbleIds($user_ids, $type);
            if(count($tokens) > 0){
                $service = new OneSignalServices($this->app_id, $this->rest_api_key);
                $service->sendNotificationToUser(OneSignalMessage::sendUser(
                    $inputs['title'],
                    $inputs['content'],
                    $tokens,
                    json_decode($inputs['data'], true)
                ));
            }
        }
    }

    public function sendGeneralAllNotification($type, $inputs){
        if(config('constants.send_notification') == 1){
//            $this->setAppKey();
            $service = new OneSignalServices($this->app_id, $this->rest_api_key);
            $service->sendNotificationToAll(OneSignalMessage::sendAll(
                $inputs['title'],
                $inputs['content'],
                json_decode($inputs['data'], true)
            ));
        }
    }

    private function sendNotification($user, $type, $inputs) {
        if(config('constants.send_notification') == 1){
//            $this->setAppKey();
            $service = new OneSignalServices($this->app_id, $this->rest_api_key);
            $service->sendNotificationToUser(OneSignalMessage::sendUser(
                $inputs['title'],
                $inputs['content'],
                OneSignalMessage::getDeviceToken($user),
                json_decode($inputs['data'], true)
            ));
        }
    }

    public function sendNotificationWithToken($tokens, $type, $inputs) {
        if(config('constants.send_notification') == 1){
//            $this->setAppKey();
            $service = new OneSignalServices($this->app_id, $this->rest_api_key);
            $service->sendNotificationToUser(OneSignalMessage::sendUser(
                $inputs['title'],
                $inputs['content'],
                $tokens,
                json_decode($inputs['data'], true)
            ));
        }
    }

    public function findDeviceTokens($user_ids) {
        return $this->repo_device->findTokenByUserIds($user_ids);
    }

    public function setAppKey() {
        $this->app_id = config('constants.one_signal.app_id');
        $this->rest_api_key = config('constants.one_signal.rest_api_key');
    }
}
