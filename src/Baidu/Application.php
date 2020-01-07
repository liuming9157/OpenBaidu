<?php
namespace Openplatform\Baidu;

use Openplatform\Baidu\AesDecryptUtil;
use Openplatform\Baidu\MsgSignatureUtil;
use GuzzleHttp\Client;
use think\Controller;
use think\Db;
use think\Request;
use think\Cache;

class Application extends Controller
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
     * 获取加密Ticket并缓存10分钟
     *
     * @return void
     * @author
     **/
    public function serve()
    {
        if(Request::instance()->isPost()){
            $baiduTicket=Request::instance()->post('Encrypt');
            Cache::set('baiduTicket',$baiduTicket,600);
        }
        return 'success';
        
        
        
    }

    /**
     * 解密并获取Ticket
     *
     * @return void
     * @author
     **/
    public function ticket()
    {
        $encrypt      = Cache::get('baiduTicket'); //获取缓存中的加密信息
        $decryptUtil = new AesDecryptUtil($this->encodingAesKey); //解密工具
        $decryptData = $decryptUtil->decrypt($encrypt); //对数据解密
        $ticket       = json_decode($decryptData)->Ticket; //获取ticket
        return $ticket;
    }
    /**
     * 直接从百度服务器获取第三方平台AccessToken
     * @param client_id 
     * @param ticket
     * @return token 
     * @author
     **/
    public function tpToken(){
            //请求百度接口
            $response = $this->client->get('/public/2.0/smartapp/auth/tp/token', [
                'query' => [
                    'client_id' => $this->client_id,
                    'ticket'    => $this->ticket(),

                ],

            ]);
            $responseData = $response->getBody()->getContents(); //百度返回信息
            $responseData = json_decode($responseData);
            if ($responseData->errno == 0) {
                $token = $responseData->data->access_token; //对返回信息进行处理并获取token
                return $token;
            } else {
                return $resonose->msg;
            }
    }
   
  
    /**
     * 获取预授权码pre_auth_code
     * @param toToken
     * @return $pre_auth_code
     * @author
     **/
    public function preAuthCode($tpToken)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/tp/createpreauthcode', [
            'query' => [
                'access_token' => $tpToken,

            ],

        ]);
        $responseData  = $response->getBody()->getContents(); //百度返回信息
        $pre_auth_code = json_decode($responseData)->data->pre_auth_code; //对返回信息进行处理并获取token
        return $pre_auth_code;
    }
    /**
     * 跳转到授权页
     * @param tpToken 
     * @return void
     * @author
     **/
    public function jump($tpToken)
    {

        $url='https://smartprogram.baidu.com/mappconsole/tp/authorization?client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_uri . '&pre_auth_code=' . $this->preAuthCode($tpToken);
        echo "<script language='javascript' type='text/javascript'>window.location.href = '$url'</script>";

    }
    /**
     * 获取授权码
     *
     * @return void
     * @author
     **/
    public function authCode()
    {
        $auth_code     = Request::instance()->param('authorization_code');
        return $auth_code;

    }
    /**
     * 找回授权码,丢失refresh_token时使用
     * @param tpToken string
     * @param appid string
     * @return auth_code string
     * @author
     **/
    public function findAuthCode($tpToken,$appid)
    {//请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/auth/retrieve/authorizationcode', [
            'query' => [
                'access_token' => $tpToken,
                'app_id'         => $appid,
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData=json_decode($responseData);
        if($responseData->errno==0){
            $auth_code=$responseData->data->authorization_code;
            return $auth_code;
        }else{
            throw new Exception($responseData->msg, 1);
            
        }
        

    }
    /**
     * 授权时或丢失refresh_token时调用，从百度服务器获取授权小程序AccessToken及RefreshToken
     * 注意：小程序AccessToken和第三方平台AccessToken是不一样的
     * @param tpToken
     * @param authCode
     * @return object(acces_token,refresh_token,expires_in)
     * @author
     **/
    public function mpToken($tpToken,$authCode)
    {
       
        //请求百度接口
        $response = $this->client->get('/rest/2.0/oauth/token', [
            'query' => [
                'access_token' => $tpToken,
                'code'         => $authCode,
                'grant_type'   => 'app_to_tp_authorization_code',
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData=json_decode($responseData);
        return $responseData;

    }
     /**
     * 刷新授权小程序AccessToken
     * 注意：小程序AccessToken和第三方平台AccessToken是不一样的
     * @param refresh_token
     * @param tp_token
     * @return object(access_token,refresh_token)
     * @author
     **/
    public function refreshToken($refresh_token,$tpToken)
    {
      
        //请求百度接口
        $response = $this->client->get('/rest/2.0/oauth/token', [
            'query' => [
                'access_token' => $tpToken,
                'refresh_token'         => $refresh_token,
                'grant_type'   => 'app_to_tp_refresh_token',
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData  = json_decode($responseData);
        return $responseData;

        
    }

    /**
     * 获取小程序基础信息
     * @param $mpToken string
     * @return mpInfo object
     * @author
     **/
    public function mpInfo($mpToken)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/app/info', [
            'query' => [
                'access_token' => $mpToken,

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $mpInfo       = json_decode($responseData); //对返回信息进行处理
        return $mpInfo; //具体字段可参考文档https://smartprogram.baidu.com/docs/develop/third/pro/

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
