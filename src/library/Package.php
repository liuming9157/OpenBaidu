<?php
namespace Openplatform\Baidu;


use GuzzleHttp\Client;
use think\Controller;
use think\Db;
use think\Request;
use think\Cache;

class Package
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
 

     /**
     * 上传小程序代码
     * @param $mpToken string
     * @param $template_id int
     * @param $ext_json string
     * @param $user_version string
     * @param $user_desc string
     * @return string
     * @author
     **/
    public function uploadCode($mpToken,$template_id,$ext_json,$user_version,$user_desc)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/package/upload', [
            'form_params' => [
                'access_token' => $mpToken,
                'template_id'=>$template_id,//模板ID
                'ext_json'=>$ext_json,//自定义配置
                'user_version'=>$user_version,//代码版本号
                'user_desc'=>$user_desc//代码描述

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; 

    }
     
     /**
     * 提交审核
     * @param $mpToken string
     * @param $package_id string
     * @param $content string
     * @param $remark string
     * @return string
     * @author
     **/
    public function submitAudit($mpToken,$package_id,$content='',$remark='')
    {
        
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/package/submitaudit', [
            'form_params' => [
                'access_token' => $mpToken,
                'package_id'=>$package_id,//包ID
                'content'=>$content,//送审描述
                'remark'=>$remark,//送审备注
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; //

    }
     /**
     * 发布代码
    * @param $mpToken string
     * @param $package_id string
     * @return string
     * @author
     **/
    public function releaseCode($mpToken,$package_id)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/package/release', [
            'form_params' => [
                'access_token' => $mpToken,
                'package_id'=>$package_id,//包ID
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; //

    }
    /**
     * 回滚包
     *
     * @return void
     * @author 
     **/
    function rollback()
    {
    }
    /**
     * 撤销审核
     *
     * @return void
     * @author 
     **/
    function withdraw()
    {
    }
    /**
     * 查询代码包
     * @param $mpToken string
     * @return object
     * @author
     **/
    public function package($mpToken)
    {
        
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/package/get', [
            'query' => [
                'access_token' => $mpToken
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData      = json_decode($responseData); //对返回信息进行处理
        return $responseData; //

    }
    /**
     * 获取小程序包详情
     *
     * @return void
     * @author 
     **/
    function getDetail()
    {
    }
     /**
     * 修改服务器域名，直接调用此接口，可自动修改授权小程序服务器域名
     * @param $mpToken
     * @return string
     * @author
     **/
    public function modifyDomain($mpToken)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/app/modifydomain', [
            'form_params' => [
                'access_token' => $mpToken,
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; //

    }
    /**
     * 获取二维码图片
     * @param $mpToken
     * @param $package_id
     * @return string
     * @author
     **/
    public function qrcode($mpToken,$package_id='',$size=200)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/app/qrcode', [
            'query' => [
                'access_token' => $mpToken,
                'package_id'   => $package_id,
                'size'         => $size        
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; //

    }
    /**
     * 申请手机号权限
     * @param $mpToken
     * @param $reason int
     * @param $used_scene int
     * @param $scene_desc string
     * @param $scene_demo string 图片链接地址，需调用图片上传接口获取
     * @return success string
     * @author
     **/
    public function mobile($mpToken,$reason=0,$used_scene=0,$scene_desc='test',$scene_demo='')
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/app/apply/mobileauth', [
            'form_params' => [
                'access_token' => $mpToken,
                'reason'       => $reason,
                'used_scene'   => $used_scene,
                'scene_desc'   => $scene_desc,
                'scene_demo'   =>$scene_demo     
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; //

    }

    /**
     * 验证签名
     *
     * @return boleen
     * @author 
     **/
    public function check()
    {
        $token=$this->token;
        $timestamp=Request::instance()->param('timestamp');
        $nonce=Request::instance()->param('nonce');
        $encrpt_msg=Request::instance()->param('encrpt_msg');
        $signature=Request::instance()->param('signature');
        $util=new MsgSignatureUtil();
        $signature2=$util->getMsgSignature($token,$timestamp,$nonce,$encrpt_msg);
        if($signature==$signature){
            return true;
        }else{
            return false;
        }
    }


}
