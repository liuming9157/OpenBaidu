<?php
namespace Openplateform;
use Openplateform\baidu\Application as Baidu;
use Openplateform\wechat\Application as Wechat;
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