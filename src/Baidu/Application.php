<?php
namespace Openplatform\Baidu;

use Openplatform\Baidu\AesDecryptUtil;
use GuzzleHttp\Client;
use think\Controller;
use think\Db;
use think\Cache;

class Application extends Controller
{
    private $encodingAesKey = ''; //第三方平台AesKey
    private $client_id      = ''; //第三方平台appsecret;
    private $redirect_uri   = ''; //授权后回调URI;
    private $base_uri       = 'https://openapi.baidu.com/'; //百度接口基础URI
    private $client;

    public function __construct($config = [])
    {
        $this->encodingAesKey = $config['encodingAesKey'];
        $this->client_id      = $config['client_id'];
        $this->redirect_uri   = $config['redirect_uri'];
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
        if($this->request->post('Nonce')){
            $baiduTicket=$this->request->post('Encrypt');
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
    public function getTicket()
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
                    'ticket'    => $this->getTicket(),

                ],

            ]);
            $responseData = $response->getBody()->getContents(); //百度返回信息
            $responseData = json_decode($responseData);
            if ($responseData->errno == 0) {
                $token = json_decode($responseData)->data->access_token; //对返回信息进行处理并获取token
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
    public function getPreAuthCode($tpToken)
    {
        //请求百度接口
        $response = $client->get('/rest/2.0/smartapp/tp/createpreauthcode', [
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
    public function goAuthPage($tpToken='')
    {

        if (!$this->getPreAuthCode()) {
            echo '无预授权码pre_auth_code';
            return false;
        }
        $this->success('稍后请扫码授权', 'https://smartprogram.baidu.com/mappconsole/tp/authorization?client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_uri . '&pre_auth_code=' . $this->getPreAuthCode($tpToken));

    }
    /**
     * 获取授权码
     *
     * @return void
     * @author
     **/
    public function getAuthCode()
    {
        $param     = $this->request->param();
        $auth_code = $param->authorization_code;
        return $auth_code;

    }
    /**
     * 从百度服务器获取授权小程序AccessToken及RefreshToken
     * 注意：小程序AccessToken和第三方平台AccessToken是不一样的
     * @param tpToken
     * @return object(acces_token,refresh_token,expires_in)
     * @author
     **/
    public function mpToken($tpToken='')
    {
       
        //请求百度接口
        $response = $this->client->get('/rest/2.0/oauth/token', [
            'query' => [
                'access_token' => $tpToken,
                'code'         => $this->getAuthCode(),
                'grant_type'   => 'app_to_tp_authorization_code',
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        return $responseData;

    }
     /**
     * 刷新授权小程序AccessToken
     * 注意：小程序AccessToken和第三方平台AccessToken是不一样的
     * @param refresh_token
     * @return object(access_token,refresh_token)
     * @author
     **/
    public function refreshMpToken($refresh_token='')
    {
      
        //请求百度接口
        $response = $this->client->get('/rest/2.0/oauth/token', [
            'query' => [
                'access_token' => $this->getTpToken(),
                'refresh_token'         => $refresh_token,
                'grant_type'   => 'app_to_tp_refresh_token',
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        return $responseData;

        
    }

    /**
     * 获取小程序基础信息
     *
     * @return void
     * @author
     **/
    public function getMpInfo()
    {
        $access_token=$this->getMpToken();
        $client = new Client([
            'base_uri' => $this->base_uri,
        ]);
        //请求百度接口
        $response = $client->get('/rest/2.0/smartapp/app/info', [
            'query' => [
                'access_token' => $access_token,

            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $mpInfo       = json_decode($responseData); //对返回信息进行处理
        $app_id=Db::name('mp_token')->where('access_token',$access_token)->where('type','baidu')->value('app_id');
        if(!$app_id){
            $app_id=$mpInfo->app_id;
           Db::name('mp_token')->where('access_token',$access_token)->where('type','baidu')->update(['app_id'=>$app_id]); 
        }
        return $mpInfo; //具体字段可参考文档https://smartprogram.baidu.com/docs/develop/third/pro/

    }
     /**
     * 上传小程序代码
     * @param $template_id int
     * @param $ext_json string
     * @param $user_version string
     * @param $user_desc string
     * @return string
     * @author
     **/
    public function uploadCode($template_id,$ext_json,$user_version,$user_desc)
    {
        $client = new Client([
            'base_uri' => $this->base_uri,
        ]);
        //请求百度接口
        $response = $client->post('/rest/2.0/smartapp/package/upload', [
            'data' => [
                'access_token' => $this->getMpToken(),
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
     * @param $package_id string
     * @param $content string
     * @param $remark string
     * @return string
     * @author
     **/
    public function submitAudit($package_id,$content='',$remark='')
    {
        $client = new Client([
            'base_uri' => $this->base_uri,
        ]);
        //请求百度接口
        $response = $client->post('/rest/2.0/smartapp/package/submitaudit', [
            'data' => [
                'access_token' => $this->getMpToken(),
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
     * @param $package_id string
     * @return string
     * @author
     **/
    public function releaseCode($package_id)
    {
        $client = new Client([
            'base_uri' => $this->base_uri,
        ]);
        //请求百度接口
        $response = $client->post('/rest/2.0/smartapp/package/release', [
            'data' => [
                'access_token' => $this->getMpToken(),
                'package_id'=>$package_id,//包ID
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; //

    }
     /**
     * 修改服务器域名，直接调用此接口，可自动修改授权小程序服务器域名
     * @param $access_token
     * @return string
     * @author
     **/
    public function modifyDomain()
    {
        $client = new Client([
            'base_uri' => $this->base_uri,
        ]);
        //请求百度接口
        $response = $client->post('/rest/2.0/smartapp/app/modifydomain', [
            'data' => [
                'access_token' => $this->getMpToken(),
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; //

    }


}
