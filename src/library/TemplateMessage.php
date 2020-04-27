<?php
namespace Openplatform\Baidu;


use GuzzleHttp\Client;
use think\Cache;

class TemplateMessage
{
    
    private $base_uri       = 'https://openapi.baidu.com/'; //百度接口基础URI
    private $client;
    private $mpToken;

    public function __construct($mpToken)
    {
        
        $this->mpToken          = $mpToken;
        $this->client         = new Client([
                'base_uri' => $this->base_uri,
            ]);
    }
    /**
     * 获取模板列表
     * @param mpToken
     * @param offset
     * @param count
     * @return object
     * @author 
     **/
    public function templateList($offset=0,$count=20)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/template/library/list', [
            'query' => [
                'access_token' => $this->mpToken(),
                'offset'=>$offset,
                'count' =>$count

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData = json_decode($responseData)->data; //对返回信息进行处理
        return $responseData;
    }
    /**
     * 获取关键词库
     * @param mpToken
     * @param id
     * @return object
     * @author 
     **/
    public function keywords($id)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/template/library/get', [
            'query' => [
                'access_token' => $this->mpToken(),
                'id'=>$id
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData = json_decode($responseData)->data; //对返回信息进行处理
        return $responseData;
    }
    /**
     * 增加模板
     * @param mpToken
     * @param id
     * @return object
     * @author 
     **/
    public function addTemplate($id,$keyword_id_list)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/template/add', [
            'form_params' => [
                'access_token' => $this->mpToken(),
                'id'=>$id,
                'keyword_id_list'=>$keyword_id_list
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData = json_decode($responseData)->data; //对返回信息进行处理
        return $responseData;
    }
    /**
     * 增加模板
     * @param mpToken
     * @param template_id
     * @return object
     * @author 
     **/
    public function delTemplate($template_id)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/template/add', [
            'form_params' => [
                'access_token' => $this->mpToken(),
                'template_id'=>$template_id
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData = json_decode($responseData)->data; //对返回信息进行处理
        return $responseData;
    }
    /**
     * 发送模板消息
     * @param mpToken
     * @param template_id
     * @return object
     * @author 
     **/
    public function sendMessage($template_id,$touser,$data,$scene_id,$scene_type='1')
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/template/add', [
            'form_params' => [
                'access_token' => $this->mpToken(),
                'template_id'=>$template_id,
                'touser'=>$touser,
                'data'=>$data,
                $scene_id=$scene_id,
                'scene_type'=>$scene_type
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData = json_decode($responseData)->data; //对返回信息进行处理
        return $responseData;
    }
}
