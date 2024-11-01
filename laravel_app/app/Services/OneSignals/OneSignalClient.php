<?php
/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 4/9/2019
 * Time: 12:44 PM
 */

namespace App\Services\OneSignals;

use GuzzleHttp\Client;

class OneSignalClient
{
    /**
     *
     */
    const API_URL = "https://onesignal.com/api/v1";
    /**
     *
     */
    const ENDPOINT_NOTIFICATIONS = "/notifications";
    /**
     *
     */
    const ENDPOINT_PLAYERS = "/players";
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var array
     */
    protected $headers;
    /**
     * @var
     */
    protected $appId;
    /**
     * @var
     */
    protected $restApiKey;
    /**
     * @var
     */
    protected $userAuthKey;
    /**
     * @var array
     */
    protected $additionalParams;
    /**
     * @var bool
     */
    public $requestAsync = false;
    /**
     * @var Callable
     */
    private $requestCallback;
    /**
     * Turn on, turn off async requests
     *
     * @param bool $on
     * @return $this
     */
    public function async($on = true)
    {
        $this->requestAsync = $on;
        return $this;
    }
    /**
     * Callback to execute after OneSignal returns the response
     * @param Callable $requestCallback
     * @return $this
     */
    public function callback(Callable $requestCallback)
    {
        $this->requestCallback = $requestCallback;
        return $this;
    }

    /**
     * OneSignalClient constructor.
     * @param $appId
     * @param $restApiKey
     * @param $userAuthKey
     */
    public function __construct($appId, $restApiKey, $userAuthKey)
    {
        $this->appId = $appId;
        $this->restApiKey = $restApiKey;
        $this->userAuthKey = $userAuthKey;
        $this->client = new Client();
        $this->headers = ['headers' => []];
        $this->additionalParams = [];
    }

    /**
     * @return string
     */
    public function testCredentials() {
        return "APP ID: ".$this->appId." REST: ".$this->restApiKey;
    }

    /**
     *
     */
    private function requiresAuth() {
        $this->headers['headers']['Authorization'] = 'Basic '.$this->restApiKey;
    }

    /**
     *
     */
    private function usesJSON() {
        $this->headers['headers']['Content-Type'] = 'application/json';
    }

    /**
     * @param array $params
     * @return $this
     */
    public function addParams($params = [])
    {
        $this->additionalParams = $params;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->additionalParams[$key] = $value;
        return $this;
    }

    /**
     * @param $message
     * @param $title
     * @param $userId
     * @param null $url
     * @param null $data
     * @param null $buttons
     * @param null $schedule
     * @param null $image
     * @param bool $asyn
     */
    public function sendNotificationToUser($message, $title, $userId, $url = null, $data = null, $buttons = null, $schedule = null, $image = null, $asyn = false) {
        $params = array(
            'app_id' => $this->appId,
            'contents' => $message,
            'headings' => $title,
            'include_player_ids' => is_array($userId) ? $userId : array($userId)
        );
        $params += $this->setParams($url, $data, $buttons, $schedule, $image);

        if(isset($asyn) && $asyn){
            $this->async(true);
        }

        $this->sendNotificationCustom($params);
    }

    /**
     * @param $message
     * @param $title
     * @param $tags
     * @param null $url
     * @param null $data
     * @param null $buttons
     * @param null $schedule
     * @param null $image
     * @param bool $asyn
     */
    public function sendNotificationUsingTags($message, $title, $tags, $url = null, $data = null, $buttons = null, $schedule = null, $image = null, $asyn = false) {
        $contents = $message;
        $params = array(
            'app_id' => $this->appId,
            'contents' => $contents,
            'headings' => $title,
            'filters' => $tags,
        );
        $params += $this->setParams($url, $data, $buttons, $schedule, $image);

        if(isset($asyn) && $asyn){
            $this->async(true);
        }

        $this->sendNotificationCustom($params);
    }

    /**
     * @param $message
     * @param $title
     * @param null $url
     * @param null $data
     * @param null $buttons
     * @param null $schedule
     * @param null $image
     * @param bool $asyn
     */
    public function sendNotificationToAll($message, $title, $url = null, $data = null, $buttons = null, $schedule = null, $image = null, $asyn = false) {
        $contents = $message;
        $params = array(
            'app_id' => $this->appId,
            'contents' => $contents,
            'headings' => $title,
            'included_segments' => array('All')
        );
        $params += $this->setParams($url, $data, $buttons, $schedule, $image);

        if(isset($asyn) && $asyn){
            $this->async(true);
        }

        $this->sendNotificationCustom($params);
    }

    /**
     * @param $message
     * @param $title
     * @param $segment
     * @param null $url
     * @param null $data
     * @param null $buttons
     * @param null $schedule
     * @param null $image
     * @param bool $asyn
     */
    public function sendNotificationToSegment($message, $title, $segment, $url = null, $data = null, $buttons = null, $schedule = null, $image = null, $asyn = false) {
        $contents = $message;
        $params = array(
            'app_id' => $this->appId,
            'contents' => $contents,
            'headings' => $title,
            'included_segments' => [$segment]
        );
        $params += $this->setParams($url, $data, $buttons, $schedule, $image);

        if(isset($asyn) && $asyn){
            $this->async(true);
        }

        $this->sendNotificationCustom($params);
    }

    /**
     * Send a notification with custom parameters defined in
     * https://documentation.onesignal.com/reference#section-example-code-create-notification
     * @param array $parameters
     * @return mixed
     */
    public function sendNotificationCustom($parameters = []){
        $this->requiresAuth();
        $this->usesJSON();
        if (isset($parameters['api_key'])) {
            $this->headers['headers']['Authorization'] = 'Basic '.$parameters['api_key'];
        }
        // Make sure to use app_id
        if (!isset($parameters['app_id'])) {
            $parameters['app_id'] = $this->appId;
        }
        // Make sure to use included_segments
        if (empty($parameters['included_segments']) && empty($parameters['include_player_ids'])) {
            $parameters['included_segments'] = ['all'];
        }
        $parameters = array_merge($parameters, $this->additionalParams);
        $this->headers['body'] = json_encode($parameters);
        $this->headers['buttons'] = json_encode($parameters);
        $this->headers['verify'] = false;
        return $this->post(self::ENDPOINT_NOTIFICATIONS);
    }
    /**
     * get devices in apps
     * @param $offset, $limit
     * @return mixed
     */
    public function getNotificationDevices($offset, $limit){
        $this->requiresAuth();
        $this->usesJSON();
        return $this->get(self::ENDPOINT_PLAYERS
            . '?app_id='. $this->appId
            . '&limit=' . $limit
            . '&offset=' . $offset);
    }

    /**
     * @param $notification_id
     * @param null $app_id
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getNotification($notification_id, $app_id = null) {
        $this->requiresAuth();
        $this->usesJSON();
        if(!$app_id)
            $app_id = $this->appId;
        return $this->get(self::ENDPOINT_NOTIFICATIONS . '/'.$notification_id . '?app_id='.$app_id);
    }
    /**
     * Creates a user/player
     *
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function createPlayer(Array $parameters) {
        if(!isset($parameters['device_type']) or !is_numeric($parameters['device_type'])) {
            throw new \Exception('The `device_type` param is required as integer to create a player(device)');
        }
        return $this->sendPlayer($parameters, 'POST', self::ENDPOINT_PLAYERS);
    }
    /**
     * Edit a user/player
     *
     * @param array $parameters
     * @return mixed
     */
    public function editPlayer(Array $parameters) {
        return $this->sendPlayer($parameters, 'PUT', self::ENDPOINT_PLAYERS . '/' . $parameters['id']);
    }
    /**
     * Create or update a by $method value
     *
     * @param array $parameters
     * @param $method
     * @param $endpoint
     * @return mixed
     */
    private function sendPlayer(Array $parameters, $method, $endpoint)
    {
        $this->requiresAuth();
        $this->usesJSON();
        $parameters['app_id'] = $this->appId;
        $this->headers['body'] = json_encode($parameters);
        $method = strtolower($method);
        return $this->{$method}($endpoint);
    }

    /**
     * @param $endPoint
     * @return \GuzzleHttp\Promise\PromiseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function post($endPoint) {
        if($this->requestAsync === true) {
            $promise = $this->client->postAsync(self::API_URL . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->post(self::API_URL . $endPoint, $this->headers);
    }

    /**
     * @param $endPoint
     * @return \GuzzleHttp\Promise\PromiseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function put($endPoint) {
        if($this->requestAsync === true) {
            $promise = $this->client->putAsync(self::API_URL . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->put(self::API_URL . $endPoint, $this->headers);
    }

    /**
     * @param $endPoint
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($endPoint) {
        return $this->client->get(self::API_URL . $endPoint, $this->headers);
    }

    /**
     * @param $url
     * @param $data
     * @param $buttons
     * @param $schedule
     * @param $image
     * @return array
     */
    private function setParams($url, $data, $buttons, $schedule, $image){
        $sound_ios = isset($data['ios_sound']) ? $data['ios_sound'] : null;
        $sound_android = isset($data['android_channel_id']) ? $data['android_channel_id'] : null;

//        unset($data['ios_sound']);
//        unset($data['android_channel_id']);

        $new_param = [];
        // 0: high priority: 1: normal
        $new_param['priority'] = 'High';
        if (isset($url)) {
            $new_param['url'] = $url;
        }
        if (isset($data)) {
            $new_param['data'] = $data;
        }
        if (isset($buttons)) {
            $new_param['buttons'] = $buttons;
        }
        if(isset($schedule)){
            $new_param['send_after'] = $schedule;
        }
        if(isset($image)){
            // android
            $new_param['big_picture'] = $image;
            // ios
            $new_param['ios_attachments'] = ['image' => $image];
        }
//        // test color for android
//        $new_param['android_background_layout'] = [
//            'headings_color' => 'FFFE765F',
//            'contents_color' => 'FF00FF00'
//        ];
        // #FE765F
        $new_param['android_accent_color'] = 'FFFE765F';
        $new_param['android_led_color'] = 'FFFE765F';
        // sound ios
        if(isset($sound_ios)) {
            $new_param['ios_sound'] = $sound_ios;
        }
        // sound android
        if(isset($sound_android)) {
            $new_param['android_channel_id'] = $sound_android;
        }
//        $new_param['ios_sound'] = env('PUSH_NOTIFICATION_IOS_SOUND', null);
//        $new_param['android_channel_id'] = 'be4a8044-bbd6-11e4-a581-000c2940e62c';
        return $new_param;
    }
}
