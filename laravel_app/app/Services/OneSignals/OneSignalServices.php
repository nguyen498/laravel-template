<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 4/9/2019
 * Time: 12:43 PM
 */

namespace App\Services\OneSignals;

use App\Utils\LogHelper;

class OneSignalServices
{
    /**
     * @var OneSignalClient
     */
    protected $signalClient;

    /**
     * OneSignalServices constructor.
     * @param string $APP_ID
     * @param string $REST_API_KEY
     */
    public function __construct(
        string $APP_ID,
        string $REST_API_KEY
    )
    {
        $this->signalClient = new OneSignalClient(
            $APP_ID,
            $REST_API_KEY,
            config('constants.one_signal.auth_key'));
    }

    /**
     * @param $inputs
     * @return array
     */
    public function sendNotificationToAll($inputs)
    {
        try {
            $data_obj = isset($inputs['data']) ? $inputs['data'] : null;
            $image_url = isset($inputs['image_url']) ? $inputs['image_url'] : null;
            $title = isset($inputs['title']) ? $inputs['title'] : null;
            $asyn = isset($inputs['asyn']) ? $inputs['asyn'] : false;
            $this->signalClient->sendNotificationToAll($inputs['message'], $title, $url = null, $data = $data_obj, $buttons = null, $schedule = null, $image_url, $asyn);
            return [
                'code' => '200',
                'message' => 'successed send all notification'
            ];
        } catch(\Exception $e){
            $log_error = 'can not send all notification ' . $e->getMessage();
            LogHelper::writeLog($log_error, 0);
            $log_error = LogHelper::writeLog($log_error, 0);
            return [
                'code' => '090',
                'message' => $log_error
            ];
        }
    }

    /**
     * @param $inputs
     * @return array
     */
    public function sendNotificationToUser($inputs)
    {
        try {
            $data_obj = isset($inputs['data']) ? $inputs['data'] : null;
            $image_url = isset($inputs['image_url']) ? $inputs['image_url'] : null;
            $asyn = isset($inputs['asyn']) ? $inputs['asyn'] : false;
            $title = isset($inputs['title']) ? $inputs['title'] : null;
            $this->signalClient->sendNotificationToUser($inputs['message'], $title, $inputs['ids'], $url = null, $data = $data_obj, $buttons = null, $schedule = null, $image_url, $asyn);
            return [
                'code' => '200',
                'message' => 'successed send user notification'
            ];
        } catch(\Exception $e){
            $log_error = 'can not send to user notification ' . $e->getMessage();
            LogHelper::writeLog($log_error, 0);
            $log_error = LogHelper::writeLog($log_error, 0);
            return [
                'code' => '090',
                'message' => $log_error
            ];
        }
    }

    /**
     * @param $inputs
     * @return array
     */
    public function sendNotificationToSegment($inputs)
    {
        try {
            $data_obj = isset($inputs['data']) ? $inputs['data'] : null;
            $image_url = isset($inputs['image_url']) ? $inputs['image_url'] : null;
            $asyn = isset($inputs['asyn']) ? $inputs['asyn'] : false;
            $title = isset($inputs['title']) ? $inputs['title'] : null;
            $this->signalClient->sendNotificationToSegment($inputs['message'], $title, $inputs['segment'], $url = null, $data = $data_obj, $buttons = null, $schedule = null, $image_url, $asyn);
            return [
                'code' => '200',
                'message' => 'successed send segment notification'
            ];
        } catch(\Exception $e){
            $log_error = 'can not send segment notification ' . $e->getMessage();
            $log_error = LogHelper::writeLog($log_error, 0);
            return [
                'code' => '090',
                'message' => $log_error
            ];
        }
    }

    /**
     * @param $request
     * @return array
     */
    public function getDeviceNotification($inputs)
    {
        try {
            $result = $this->signalClient->getNotificationDevices($inputs['offset'], $inputs['limit']);
            return [
                'code' => '200',
                'data' => json_decode($result->getBody()->getContents(), true)
            ];
        } catch(\Exception $e){
            $log_error = 'can not get notification devices ' . $e->getMessage();
            $log_error = LogHelper::writeLog($log_error, 0);
            return [
                'code' => '090',
                'message' => $log_error
            ];
        }
    }
}
