<?php
namespace baidu;

use baidu\AesDecryptUtil;
use GuzzleHttp\Client;
use think\Controller;
use think\Db;

class Application extends Controller
{
    private $encodingAesKey = ''; //第三方平台AesKey
    private $client_id      = ''; //第三方平台appsecret;
    private $redirect_uri   = ''; //授权后回调URI;
    private $base_uri       = 'https://openapi.baidu.com/'; //百度接口基础URI
    private $debug          = true;

    public function __construct($config = [])
    {
        $this->encodingAesKey = $config['encodingAesKey'];
        $this->client_id      = $config['client_id'];
        $this->redirect_uri   = $config['redirect_uri'];
        $this->debug          = $config['debug'] ?: true;
    }
    /**
     * 获取加密Ticket并缓存10分钟
     *
     * @return void
     * @author
     **/
    public function serve()
    {
        echo 'success';
        if($this->request->post('Nonce')){
            $baiduTicket=$this->request->post('Encrypt');
            cache('baiduTicket',$baiduTicket,600);
        }
        
        
    }

    /**
     * 解密并获取Ticket
     *
     * @return void
     * @author
     **/
    public function getTicket()
    {
        $encrypt      = cache('baiduTicket'); //加密信息
        $decryptUtil = new AesDecryptUtil($this->encodingAesKey); //解密工具
        $decryptData = $decryptUtil->decrypt($encrypt); //对数据解密
        $ticket       = json_decode($decryptData)->Ticket; //获取ticket
        return $ticket;
    }
    /**
     * 直接从百度服务器获取第三方平台AccessToken
     *
     * @return void
     * @author
     **/
    public function tpToken(){
         $client = new Client([
                'base_uri' => $this->base_uri,
            ]);
            //请求百度接口
            $response = $client->get('/public/2.0/smartapp/auth/tp/token', [
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
     * 获取第三方平台AccessToken
     *
     * @return void
     * @author
     **/
    public function getTpToken()
    {
        if ($this->debug == true) {
            $this->tpToken();
        }
        if ($this->debug == false) {
            $data = Db::name('tp_token')->where('type', 'baidu')->find();
            $now          = time();
            //数据库没有AccessToken时请求接口
            if (!$data) {
               $token=$this->tpToken();
               Db::name('tp_token')->insert(['token'=>$token,'type'=>'baidu','create_time'=>$now]);
               return $token;
            }
            if ($data) {
                $remain_time = $data['create_time']+2592000-$now; //计算token剩余有效期，小于1天时重新发起请求
                if ($remain_time < 86400) {
                    $token=$this->tpToken();
                    Db::name('tp_token')->where('type','baidu')->update(['token'=>$token,'create_time'=>$now]);
                     return $token;
                } else {
                    $token = $data['token'];
                    return $token;
                }
            }
        }

    }
    /**
     * 获取预授权码pre_auth_code
     *
     * @return void
     * @author
     **/
    public function getPreAuthCode()
    {
        $client = new Client([
            'base_uri' => $this->base_uri,
        ]);
        //请求百度接口
        $response = $client->get('/rest/2.0/smartapp/tp/createpreauthcode', [
            'query' => [
                'access_token' => $this->getTpToken(),

            ],

        ]);
        $responseData  = $response->getBody()->getContents(); //百度返回信息
        $pre_auth_code = json_decode($responseData)->data->pre_auth_code; //对返回信息进行处理并获取token
        return $pre_auth_code;
    }
    /**
     * 授权跳转
     *
     * @return void
     * @author
     **/
    public function goAuthPage()
    {
        if (!$this->client_id) {
            echo '无client_id';
            return false;
        }
        if (!$this->redirect_uri) {
            echo '无redirect_uri';
            return false;
        }
        if (!$this->getPreAuthCode()) {
            echo '无预授权码pre_auth_code';
            return false;
        }
        $this->success('稍后请扫码授权', 'https://smartprogram.baidu.com/mappconsole/tp/authorization?client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_uri . '&pre_auth_code=' . $this->getPreAuthCode());

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
     * @return void
     * @author
     **/
    public function mpToken()
    {
        $client = new Client([
            'base_uri' => $this->base_uri,
        ]);
        //请求百度接口
        $response = $client->get('/rest/2.0/oauth/token', [
            'query' => [
                'access_token' => $this->getTpToken(),
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
     * @return void
     * @author
     **/
    public function refreshMpToken($refresh_token)
    {
        $client = new Client([
            'base_uri' => $this->base_uri,
        ]);
        //请求百度接口
        $response = $client->get('/rest/2.0/oauth/token', [
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
     * 获取授权小程序AccessToken及RefreshToken
     * 注意：小程序AccessToken和第三方平台AccessToken是不一样的
     * @return void
     * @author
     **/
    public function getMpToken($app_id='')
    {
        if ($this->debug == true) {
            $responseData=$this->mpToken();   
            $access_token = json_decode($responseData)->access_token; //对返回信息进行处理并获取token
            return $access_token; //注意，该令牌默认有效期是1小时，如需refresh_token请从responseData中获取
        }
        if ($this->debug == false) {
            $data = Db::name('mp_token')->where('type', 'baidu')->where('appid',$appid)->find();
            $now          = time();
            //数据库没有AccessToken时请求接口
            if (!$data) {
               $responseData=$this->tpToken();
               $access_token = json_decode($responseData)->access_token;
               $refresh_token = json_decode($responseData)->refresh_token;
                $expires_in= json_decode($responseData)->expires_in;
               Db::name('tp_token')->insert(['access_token'=>$access_token,'refresh_token'=>$refresh_token,'type'=>'baidu','expires_in'=>$expires_in,'create_time'=>$now]);
               return $access_token;
            }
            if ($data) {
                $create_time=$data['create_time'];
                $expires_in=$data['expires_in'];
                if ($create_time+$expires_in>=$now) {
                    $refresh_token=$data['refresh_token'];
                    $responseData=$this->refreshTpToken($refresh_token);
                    $access_token= json_decode($responseData)->access_token;
                    $refresh_token= json_decode($responseData)->refresh_token;
                    $expires_in= json_decode($responseData)->expires_in;
                    Db::name('tp_token')->where('type','baidu')->where('appid',$appid)->update(['access_token'=>$access_token,'refresh_token'=>$refresh_token,'expires_in'=>$expires_in]);
                    return $access_token;

                } else {
                    $access_token=$data['access_token'];
                    return $access_token;
                }
            }
        }

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
