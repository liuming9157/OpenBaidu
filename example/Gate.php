<?php
namespace app\baidu;
use think\Controller;
use baidu\Application;
class Gate extends Controller{
	protected $config=[
		'encodingAesKey'=>'',
		'client_id'=>'',
		'redirect_uri'=>'',
		'debug'=>false

	];
	protected $app=null;
	public function _construct(){
		$this->app=new Application($this->config);
	}
	/**
	 * 小程序授权接收URL。
	 * @return void
	 * @author 
	 **/
	public function authNotice(){
		$this->app->serve();
	}
	public function auth(){
		$this->app->goAuthPage();
	}
	public function uri(){
		$this->app->getMpInfo();
	}
	/**
	 * 小程序审核结果推送到第三方的消息与事件接收URL。
	 * https://smartprogram.baidu.com/docs/third/apppage/#%E4%BB%A3%E7%A0%81%E5%AE%A1%E6%A0%B8%E7%8A%B6%E6%80%81%E6%8E%A8%E9%80%81
	 * @return void
	 * @author 
	 **/
	public function msgNotice(){
		$param=$this->request->param();
		//To to sth.
	}
}