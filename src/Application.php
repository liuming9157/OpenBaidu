<?php
namespace Openplatform;
use Openplatform\Baidu\Application as Baidu;
use Openplatform\Wechat\Application as Wechat;
use Exception;
class Application{
	public function __construct(){

	}
	public static function __callStatic($method,$params=[]){
		
		if(class_exists($method)){
			return new $method();
		}
		throw new Exception('Class not exists');
	}
}