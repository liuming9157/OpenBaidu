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

	public function _construct($config=[]){
		$this->encodingAesKey=$config['encodingAesKey'];
		$this->client_id=$config['client_id'];
		$this->redict_uri=$config['redirect_uri'];
	}

	/**
	 * 解密并获取Ticket
	 *
	 * @return void
	 * @author 
	 **/
	public function getTicket()
	{
		$encrypt=cache('encrypt')['Encrypt'];//加密信息
		$descryptUtil=new AesDecryptUtil($this->encodingAesKey);//解密工具
		$descryptData=$descryptUtil->decrypt($encrypt);//对数据解密
		$ticket=json_decode($descryptData)->Ticket;//获取ticket
		echo $ticket;
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
		$token=json_decode($responseData)->data->access_token;//对返回信息进行处理并获取token
		echo $token;

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
		echo $pre_auth_code;
	}
	/**
	 * 授权跳转
	 *
	 * @return void
	 * @author 
	 **/
	public function redirect()
	{
		header('location:https://smartprogram.baidu.com/mappconsole/tp/authorization?client_id='.$this->client_id.'&redirect_uri='.$this->redirect_uri.'&pre_auth_code='.$this->getPreAuthCode());

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
		echo $auth_code;

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
		echo $access_token;//注意，入需refresh_token请从responseData中获取
	}
}