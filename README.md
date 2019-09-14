### 百度智能小程序第三方平台SDK  
1. App.php是主体文件，有注释。  
在该文件中配置相关参数，也可在实例化是配置相关参数
2. AesDecryptUtil类的decrypt方法用来解密消息，根据百度提供的SDK有改写
3. MsgSignatureUtil类的getMsgSignature方法用来验证消息签名，根据百度提供的SDK有改写。
### 依赖
1. 本SDK只能在ThinkPHP框架下使用  
2. 本SDK依赖guzzle,故需通过composer安装  
### 使用方法
###### Step1  
在thinkphp中设置一个控制器，用于接收百度服务器推送的ticket加密信息  
```
public function index(){
	$res=$this->request->param();
	if($res){
		echo 'success';
		//如果没有缓存该加密信息，则缓存。如果以缓存，不进行任何操作
		if(!cache('encryptedTicket')){
			cache('encryptedTicket',$res,0)
		}
	}
}
```
###### Step2  
再设置一个控制器，用于授权跳转  
```
public function redirect(){
	$config=[
	'encodingAesKey'=>'',//设置aesKey
	'client_id'=>'',//设置第三方平台secret
	'redirect_uri'=>''//设置授权后回调URI

	]
	$app=new App($config)；
	$app->redirect();
}
```
###### step3
在授权跳转页获取授权码AuthCode
```
$config=[
	'encodingAesKey'=>'',//设置aesKey
	'client_id'=>'',//设置第三方平台secret
	'redirect_uri'=>''//设置授权后回调URI

	]
	$app=new App($config)；
	$auth_key=$app->getAuthKey();
```

获取授权码后想做啥就做啥了！