<?php
namespace Openplatform\Baidu;


use GuzzleHttp\Client;
use think\Controller;
use think\Db;
use think\Request;
use think\Cache;

class Login
{
   
    private $base_uri       = 'https://openapi.baidu.com/'; //百度接口基础URI
    private $client;
    private $mpToken;

    public function __construct($mpToken='')
    {
        
        $this->mpToken          = $mpToken;
        $this->client         = new Client([
                'base_uri' => $this->base_uri,
            ]);
    }
    /**
     * 换取session key
     *
     * @return void
     * @author 
     **/
    
    public function session($code)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/oauth/getsessionkeybycode', [
            'query' => [
                'access_token' => $this->mpToken,
                'code'=>$code,
                'grant_type'=>'authorization_code'

            ],

        ]);
        $responseData  = $response->getBody()->getContents(); //百度返回信息
        $responseData = json_decode($responseData); //对返回信息进行处理并获取token
        return $responseData;
    }
    /**
     * 获取unionid
     *
     * @return void
     * @author 
     **/
    function unionid($openid)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/unionId/get', [
            'query' => [
                'access_token' => $this->mpToken,
                'open_id' => $openid
            ],

        ]);
        $responseData  = $response->getBody()->getContents(); //百度返回信息
        $unionid = json_decode($responseData)->data->union_id; //对返回信息进行处理并获取token
        return $uionid;
    }
    
}
