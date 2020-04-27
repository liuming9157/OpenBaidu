<?php
namespace OpenBaidu;

use OpenBaidu\util\AesDecryptUtil;
use OpenBaidu\util\MsgSignatureUtil;
use GuzzleHttp\Client;
use think\Request;
use think\Cache;

class Application
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
            $encrypt=Request::instance()->post('Encrypt');
            $decryptUtil = new AesDecryptUtil($this->encodingAesKey); //解密工具
            $decryptData = $decryptUtil->decrypt($encrypt); //对数据解密
            $ticket       = json_decode($decryptData)->Ticket; //获取ticket
            Cache::set('baiduTicket',$ticket,600);
        }
        return 'success';
        
        
        
    }

    
    /**
     * 获取第三方平台AccessToken
     * @param client_id 
     * @param ticket
     * @return token 
     * @author
     **/
    public function tpToken(){
        if(Cache::get('baiduTpToken')==null){
                //请求百度接口
                $response = $this->client->get('/public/2.0/smartapp/auth/tp/token', [
                'query' => [
                    'client_id' => $this->client_id,
                    'ticket'    => Cache::get('baiduTicket'),

                ],

                ]);
                $responseData = $response->getBody()->getContents(); //百度返回信息
                $responseData = json_decode($responseData);
                if ($responseData->errno == 0) {
                $token = $responseData->data->access_token; //对返回信息进行处理并获取token
                Cache::set('baiduTpToken',$token,2592000);//缓存30天
                
                } else {
                return $resonose->msg;
                }
        }
        return Cache::get('baiduTpToken');
            
    }
   
  
    /**
     * 获取预授权码pre_auth_code
     * @return $pre_auth_code
     * @author
     **/
    public function preAuthCode()
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/tp/createpreauthcode', [
            'query' => [
                'access_token' => $this->tpToken(),

            ],

        ]);
        $responseData  = $response->getBody()->getContents(); //百度返回信息
        $pre_auth_code = json_decode($responseData)->data->pre_auth_code; //对返回信息进行处理并获取token
        return $pre_auth_code;
    }
    /**
     * 跳转到授权页
     * @return void
     * @author
     **/
    public function jumpToAuthPage()
    {

        $url='https://smartprogram.baidu.com/mappconsole/tp/authorization?client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_uri . '&pre_auth_code=' . $this->preAuthCode();
        echo "<script language='javascript' type='text/javascript'>window.location.href = '$url'</script>";

    }
    /**
     * 获取授权码
     *
     * @return string
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
    public function findAuthCode($appid)
    {
       
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/auth/retrieve/authorizationcode', [
            'query' => [
                'access_token' => $this->tpToken(),
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
     * 注意：小程序AccessToken(有效期1小时)和第三方平台AccessToken(有效期1个月)是不一样的,
     * @param authCode
     * @return object(acces_token,refresh_token,expires_in)
     * @author
     **/
    public function mpToken($authCode='')
    {
       
        $authCode=empty($authCode)?$this->authCode():$authCode;
        //请求百度接口
        $response = $this->client->get('/rest/2.0/oauth/token', [
            'query' => [
                'access_token' => $this->tpToken(),
                'code'         => $authCode,
                'grant_type'   => 'app_to_tp_authorization_code',
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData=json_decode($responseData,true);//建议将此处的返回数据存入数据库

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
    public function refreshToken($refresh_token)
    {
      
        //请求百度接口
        $response = $this->client->get('/rest/2.0/oauth/token', [
            'query' => [
                'access_token' => $this->tpToken(),
                'refresh_token'         => $refresh_token,
                'grant_type'   => 'app_to_tp_refresh_token',
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData  = json_decode($responseData，true);
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
        $mpInfo       = json_decode($responseData,true); //对返回信息进行处理,建议存入数据库
        return $mpInfo; //具体字段可参考文档https://smartprogram.baidu.com/docs/develop/third/pro/

    }
    /**
     * 获取模板列表
     * @param page
     * @param page_size
     * @return object
     * @author 
     **/
    public function templateList($page=1,$page_size=10)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/template/gettemplatelist', [
            'query' => [
                'access_token' => $this->tpToken(),
                'page'         =>$page,
                'page_size'    =>$page_size

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $templateList       = json_decode($responseData,true); //对返回信息进行处理
        return $templateList;
    }
    /**
     * 删除模板
     * @param mpToken
     * @param template_id
     * @return object
     * @author 
     **/
    public function delTemplate($template_id)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/template/deltemplate', [
            'form_params' => [
                'access_token' => $this->tpToken(),
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
    public function draftList($page=1,$page_size=10)
    {
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/template/getdraftlist', [
            'query' => [
                'access_token' => $this->tpToken(),
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
    public function addTemplate($draft_id,$user_desc)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/template/gettemplatelist', [
            'form_params' => [
                'access_token' => $this->tpToken(),
                'draft_id'         =>$draft_id,
                'user_desc'    =>$user_desc

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $template_id       = json_decode($responseData)->data->template_id; //对返回信息进行处理
        return $template_id;
    }
    /**
     * 图片上传
     * @param tpToken
     * @param multipartFile
     * @param type
     * @return object
     * @author 
     **/
    public function uploadImage($multipartFile='jpg',$type='')
    {
        //请求百度接口
        $response = $this->client->post('/file/2.0/smartapp/upload/image ', [
            'form_params' => [
                'access_token' => $this->tpToken(),
                'multipartFile'=>$multipartFile,
                'type'    =>$type

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $image_url = json_decode($responseData)->data; //对返回信息进行处理
        return $image_url;
    }

    public function __call($method,$args){
        $method='OpenBaidu\\library\\'.ucfirst($method);
        if(class_exists($method)){
            return new $method(...$args);
        }
        throw new Exception('Class not exists');
    }


}
