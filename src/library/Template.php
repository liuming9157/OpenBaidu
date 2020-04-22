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
