<?php

namespace App\Services\Client;

use App\Services\Base\ClientServices;

class KeywordClientService
{
    protected $base_url;
    protected $api_token;

    public function __construct() {
        $this->api_token        = env('KEYWORD_SERVER_API', '');
        $this->base_url         = env('KEYWORD_SERVER_HOST', '');
    }

    public function createKeywordWithPost($inputs){
        $inputs['is_app'] = false;
        $clientServices = new ClientServices();
        $clientServices->setUrl($this->base_url . "/kw/posts/{$inputs['post_id']}");
        $data = $clientServices->sendPost([
            'header' => [
                'Authorization' => 'Bearer ' . $this->api_token
            ],
            'body' => [
                'text' => $inputs['text']
            ]
        ]);
        return $data;
    }

    public function deleteKeywordWithPost($inputs){
        $inputs['is_app'] = false;
        $clientServices = new ClientServices();
        $clientServices->setUrl($this->base_url . "/kw/posts/{$inputs['post_id']}");
        $data = $clientServices->sendDelete([
            'header' => [
                'Authorization' => 'Bearer ' . $this->api_token
            ]
        ]);
        return $data;
    }
}
