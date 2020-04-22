<?php
namespace Openplatform\Baidu\library;


use GuzzleHttp\Client;
use think\Request;
use think\Cache;

class Template
{
    
    private $token          = '';//小程序access_token
    private $base_uri       = 'https://openapi.baidu.com/'; //百度接口基础URI
    private $client;

    public function __construct($mpToken)
    {
        
        $this->token          = $mpToken;
        $this->client         = new Client([
                'base_uri' => $this->base_uri,
            ]);
    }
    
    /**
     * 获取模板列表
     * @param mpToken
     * @param page
     * @param page_size
     * @return object
     * @author 
     **/
    public function templateList($mpToken,$page=1,$page_size=10)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/template/gettemplatelist', [
            'query' => [
                'access_token' => $mpToken,
                'page'         =>$page,
                'page_size'    =>$page_size

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $templateList       = json_decode($responseData); //对返回信息进行处理
        return $templateList;
    }
    /**
     * 删除模板
     * @param mpToken
     * @param template_id
     * @return object
     * @author 
     **/
    public function delTemplate($mpToken,$template_id)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/template/deltemplate', [
            'form_params' => [
                'access_token' => $mpToken,
                'template_id'  =>$template_id,

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData       = json_decode($responseData); //对返回信息进行处理
        return $responseData->msg;
    }
    /**
     * 获取草稿列表
     * @param mpToken
     * @param page
     * @param page_size
     * @return object
     * @author 
     **/
    public function draftList($mpToken,$page=1,$page_size=10)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/template/getdraftlist', [
            'query' => [
                'access_token' => $mpToken,
                'page'         =>$page,
                'page_size'    =>$page_size

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $draftList       = json_decode($responseData); //对返回信息进行处理
        return $draftList;
    }
    /**
     * 获取草稿至模板
     * @param mpToken
     * @param draft_id
     * @param user_desc
     * @return object
     * @author 
     **/
    public function addTemplate($mpToken,$draft_id,$user_desc)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/template/gettemplatelist', [
            'form_params' => [
                'access_token' => $mpToken,
                'draft_id'         =>$draft_id,
                'user_desc'    =>$user_desc

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $template_id       = json_decode($responseData)->data->template_id; //对返回信息进行处理
        return $template_id;
    }



}
