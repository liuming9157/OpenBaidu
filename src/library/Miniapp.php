<?php
namespace OpenBaidu\library;


use GuzzleHttp\Client;
use think\Cache;

class Miniapp
{
    
    private $base_uri       = 'https://openapi.baidu.com/'; //百度接口基础URI
    private $client;
    private $mpToken;

    public function __construct($mpToken)
    {
        $this->client         = new Client([
                'base_uri' => $this->base_uri,
            ]);
        $this->mpToken=$mpToken;
    }
    /**
     * 实例化
     *
     * @return void
     * @author
     **/
    public static function instance($config=[])
    {
        
        return new static($config);
          
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
    public function uploadCode($template_id,$ext_json,$user_version,$user_desc)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/package/upload', [
            'form_params' => [
                'access_token' => $this->mpToken,
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
    public function submitAudit($package_id,$content='',$remark='')
    {
        
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/package/submitaudit', [
            'form_params' => [
                'access_token' => $this->mpToken,
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
    public function releaseCode($package_id)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/package/release', [
            'form_params' => [
                'access_token' => $this->mpToken,
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
    function rollback($package_id)
    {
        //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/package/rollback', [
            'form_params' => [
                'access_token' => $this->mpToken,
                'package_id'=>$package_id,//包ID
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; //

    }
    /**
     * 撤销审核
     *
     * @return void
     * @author 
     **/
    function withdraw()
    {
         //请求百度接口
        $response = $this->client->post('/rest/2.0/smartapp/package/withdraw', [
            'form_params' => [
                'access_token' => $this->mpToken,
                'package_id'=>$package_id,//包ID
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $data       = json_decode($responseData); //对返回信息进行处理
        return $data->msg; //
    }
    /**
     * 查询代码包
     * @param $mpToken string
     * @return object
     * @author
     **/
    public function package()
    {
        
        //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/package/get', [
            'query' => [
                'access_token' => $this->mpToken
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
         //请求百度接口
        $response = $this->client->get('/rest/2.0/smartapp/package/getdetail', [
            'query' => [
                'access_token' => $this->mpToken
            ],

        ]);
        $responseData = $response->getBody()->getContents(); //百度返回信息
        $responseData      = json_decode($responseData); //对返回信息进行处理
        return $responseData; //

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
    /**
     * 获取小程序全类目
     *
     * @return void
     * @author
     **/
    public function categoryList()
    {
        if(Request::instance()->isPost()){
            $baiduTicket=Request::instance()->post('Encrypt');
            Cache::set('baiduTicket',$baiduTicket,600);
        }
        return 'success';
        
        
        
    }

    /**
     * 修改小程序类目
     *
     * @return void
     * @author
     **/
    public function categoryUpdate()
    {
        $encrypt      = Cache::get('baiduTicket'); //获取缓存中的加密信息
        $decryptUtil = new AesDecryptUtil($this->encodingAesKey); //解密工具
        $decryptData = $decryptUtil->decrypt($encrypt); //对数据解密
        $ticket       = json_decode($decryptData)->Ticket; //获取ticket
        return $ticket;
    }
    /**
     * 修改小程序icon
     * @param client_id 
     * @param ticket
     * @return token 
     * @author
     **/
    public function modifyHeadIcon(){
        if(Cache::get('baiduTpToken')==null){
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
                Cache::set('baiduTpToken',$token,2592000);//缓存30天
                
                } else {
                return $resonose->msg;
                }
        }
        return Cache::get('baiduTpToken');
            
    }
   
  
    /**
     * 修改功能介绍
     * @param toToken
     * @return $pre_auth_code
     * @author
     **/
    public function modifySignature()
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
     * 暂停服务
     * @param tpToken 
     * @return void
     * @author
     **/
    public function pause()
    {

        $url='https://smartprogram.baidu.com/mappconsole/tp/authorization?client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_uri . '&pre_auth_code=' . $this->preAuthCode();
        echo "<script language='javascript' type='text/javascript'>window.location.href = '$url'</script>";

    }
    /**
     * 开启服务
     *
     * @return string
     * @author
     **/
    public function resume()
    {
        $auth_code     = Request::instance()->param('authorization_code');
        return $auth_code;

    }
    /**
     * 申请恢复流量下线
     * @param tpToken string
     * @param appid string
     * @return auth_code string
     * @author
     **/
    public function applyRecovery($appid)
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
     * 二维码
     * @param authCode
     * @return object(acces_token,refresh_token,expires_in)
     * @author
     **/
    public function qrcode($authCode='')
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
     * 修改小程序名称
     * @param refresh_token
     * @param tp_token
     * @return object(access_token,refresh_token)
     * @author
     **/
    public function setNickname($refresh_token)
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
     * 设置最低基础版本库
     * @param $mpToken string
     * @return mpInfo object
     * @author
     **/
    public function setSupportVersion($mpToken)
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
     * 查询最低基础版本库
     * @param mpToken
     * @param page
     * @param page_size
     * @return object
     * @author 
     **/
    public function getSupportVersion($mpToken,$page=1,$page_size=10)
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
     * 设置小程序服务器域名
     * @param mpToken
     * @param template_id
     * @return object
     * @author 
     **/
    public function modifyDomain($mpToken,$template_id)
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
     * 设置小程序业务域名
     * @param mpToken
     * @param page
     * @param page_size
     * @return object
     * @author 
     **/
    public function modifyWebviewDomain($mpToken,$page=1,$page_size=10)
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
     * 小程序名称检测
     * @param mpToken
     * @param draft_id
     * @param user_desc
     * @return object
     * @author 
     **/
    public function checkName($mpToken,$draft_id,$user_desc)
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
     * 基本信息强制下线后修改基本信息
     * @param $mpToken string
     * @param $template_id int
     * @param $ext_json string
     * @param $user_version string
     * @param $user_desc string
     * @return string
     * @author
     **/
    public function update($mpToken,$template_id,$ext_json,$user_version,$user_desc)
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
    




}
