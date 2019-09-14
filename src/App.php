<?php
namespace baidu;
use baidu\AesDecryptUtil;
use GuzzleHttp\Client;
use think\Controller;
class App extends Controller{
	private $encodingAesKey='';//第三方平台AesKey
	private $client_id='';//第三方平台appsecret;
	private $redirect_uri='';//授权后回调URI;
	private $base_uri='https://openapi.baidu.com/';//百度接口基础URI

	public function __construct($config=[]){
		$this->encodingAesKey=$config['encodingAesKey'];
		$this->client_id=$config['client_id'];
		$this->redirect_uri=$config['redirect_uri'];
	}

	/**
	 * 解密并获取Ticket
	 *
	 * @return void
	 * @author 
	 **/
	public function getTicket()
	{
		$encrypt=cache('encryptedTicket')['Encrypt'];//加密信息
		$descryptUtil=new AesDecryptUtil($this->encodingAesKey);//解密工具
		$descryptData=$descryptUtil->decrypt($encrypt);//对数据解密
		$ticket=json_decode($descryptData)->Ticket;//获取ticket
		return $ticket;
	}
	/**
	 * 获取第三方平台AccessToken
	 *
	 * @return void
	 * @author 
	 **/
	public function getAccessToken(){
		$client=new Client([
              'base_uri'=>$this->base_uri
		]);
		//请求百度接口
		$response=$client->get('/public/2.0/smartapp/auth/tp/token',[
			'query'=>[
				'client_id'=>$this->client_id,
				'ticket'=>$this->getTicket()

			]

		]);
		$responseData=$response->getBody()->getContents();//百度返回信息
		$responseData=json_decode($responseData);
		if($responseData->errno==0){
			$token=json_decode($responseData)->data->access_token;//对返回信息进行处理并获取token
		return $token;
		}else{
			return $resonose->msg;
		}
		

	}
	/**
	 * 获取预授权码pre_auth_code
	 *
	 * @return void
	 * @author 
	 **/
	function getPreAuthCode()
	{
		$client=new Client([
              'base_uri'=>$this->base_uri
		]);
		//请求百度接口
		$response=$client->get('/rest/2.0/smartapp/tp/createpreauthcode',[
			'query'=>[
				'access_token'=>$this->getAccessToken()

			]

		]);
		$responseData=$response->getBody()->getContents();//百度返回信息
		$pre_auth_code=json_decode($responseData)->data->pre_auth_code;//对返回信息进行处理并获取token
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
		if(!$this->client_id){
			echo '无client_id';
			return false;
		}
		if(!$this->redirect_uri){
			echo '无redirect_uri';
			return false;
		}
		if(!$this->getPreAuthCode()){
			echo '无预授权码pre_auth_code';
			return false;
		}
		$this->success('稍后请扫码授权','https://smartprogram.baidu.com/mappconsole/tp/authorization?client_id='.$this->client_id.'&redirect_uri='.$this->redirect_uri.'&pre_auth_code='.$this->getPreAuthCode());

	}
	/**
	 * 获取授权码
	 *
	 * @return void
	 * @author 
	 **/
	public function getAuthCode()
	{
		$param=$this->request->param();
		$auth_code=$param->authorization_code;
		return $auth_code;

	}
	/**
	 * 获取授权小程序AccessToken及RefreshToken
	 * 注意：小程序AccessToken和第三方平台AccessToken是不一样的
	 * @return void
	 * @author 
	 **/
	public function getMpToken()
	{
		$client=new Client([
              'base_uri'=>$this->base_uri
		]);
		//请求百度接口
		$response=$client->get('/rest/2.0/oauth/token',[
			'query'=>[
				'access_token'=>$this->getAccessToken(),
				'code'=>$this->getAuthCode(),
				'grant_type'=>'app_to_tp_authorization_code'
			]

		]);
		$responseData=$response->getBody()->getContents();//百度返回信息
		$access_token=json_decode($responseData)->access_token;//对返回信息进行处理并获取token
		return $access_token;//注意，入需refresh_token请从responseData中获取
	}
	/**
	 * 获取小程序基础信息
	 *
	 * @return void
	 * @author 
	 **/
	public function getMpInfo()
	{
		$client=new Client([
              'base_uri'=>$this->base_uri
		]);
		//请求百度接口
		$response=$client->get('/rest/2.0/smartapp/app/info',[
			'query'=>[
				'access_token'=>$this->getMpToken()

			]

		]);
		$responseData=$response->getBody()->getContents();//百度返回信息
		$mpInfo=json_decode($responseData);//对返回信息进行处理
		return $mpInfo;//具体字段可参考文档https://smartprogram.baidu.com/docs/develop/third/pro/

	}
}