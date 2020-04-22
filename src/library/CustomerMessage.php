<?php
namespace Openplatform\Baidu;


use GuzzleHttp\Client;
use think\Controller;
use think\Db;
use think\Request;
use think\Cache;

class CustomerMessage
{
    private $encodingAesKey = ''; //第三方平台AesKey
    private $client_id      = ''; //第三方平台appsecret;
    private $redirect_uri   = ''; //授权后回调URI;
    private $token          = '';//消息验证token
    private $base_uri       = 'https://openapi.baidu.com/'; //百度接口基础URI
    private $client;

    public function __construct($config = [])
    {
        $this->encodingAesKey = $config['encodingAesKey'];
        $this->client_id      = $config['client_id'];
        $this->redirect_uri   = $config['redirect_uri'];
        $this->token          = $config['token'];
        $this->client         = new Client([
                'base_uri' => $this->base_uri,
            ]);
    }
    

}
