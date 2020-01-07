<?php
namespace app\baidu;
use think\Controller;
use Openplatform\Baidu\Application;
class Index extends Controller{
	protected $config=[
		'encodingAesKey'=>'',
		'client_id'=>'',
		'redirect_uri'=>'',

	];
	protected $app=null;
	public function __construct(){
		$this->app=new Application($config);
	}
	/**
	 * 平台接收ticket
	 **/
	public function notice(){
		$this->app->serve();//这个方法会返回success
	}
	/**
	 * 获取平台的access_token
	 **/
	public function getTpToken()
	{
		//我习惯将平台的access_token用tpToken表示，以便跟小程序的access_token区别
		$tpToken=$this->app->tpToken();
		//tpToken有效期为30天，建议获取后存入数据库，剩余有效期为一天时再重新获取


	}
	/**
	 * 用户点击按钮跳转到授权页
	 **/
	public function auth($tpToken){
		$this->app->jump($tpToken);
	}
	/**
	 * 用户授权完成后在回调页获取小程序的access_token
	 **/
	public function getMpToken(){
		//$tpToken=...
		//$authCode=...
		$data=$this->app->mpToken($tpToken,$authCode);
		$access_token=$data->access_token;
		$refresh_token=$data->refresh_token;
		//小程序的access_token会反复调用，有效期为1小时，refresh_token有效期为10年，建议都存入数据库，每次调用access_token前先判断是否过期，如果过期就进行刷新。
	}
	/**
	 * 小程序审核结果推送到第三方的消息与事件接收URL。
	 * https://smartprogram.baidu.com/docs/third/apppage/#%E4%BB%A3%E7%A0%81%E5%AE%A1%E6%A0%B8%E7%8A%B6%E6%80%81%E6%8E%A8%E9%80%81
	 * @return void
	 * @author 
	 **/
	public function msgNotice(){
		
		//To to sth.
	}
}