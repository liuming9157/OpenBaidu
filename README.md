### 百度智能小程序第三方平台SDK  
1. Application.php是主体文件，有注释。  
2. AesDecryptUtil类的decrypt方法用来解密消息，根据百度提供的SDK有改写
3. MsgSignatureUtil类的getMsgSignature方法用来验证消息签名，根据百度提供的SDK有改写。
### 依赖
1. 本SDK只能在ThinkPHP框架下使用  
2. 本SDK依赖guzzle,故需通过composer安装  
### 使用方法
参照`exmaple\Gate.php`中写法
```
###### 方法介绍  
```
protected $config=[
    'encodingAesKey'=>'',//设置aesKey
	'client_id'=>'',//设置第三方平台secret
	'redirect_uri'=>'',//设置授权后回调URI
	'debug'=>true//生产模式时请设置为false,并且按照文档添加数据库
     ];

$app=new App($this->config);
$app->serve();接收服务器推送时间返回success,如果是ticket则缓存ticket
$app->getTicket();//解密并获取ticket
$app->getTpToken();//获取第三方平台的AccessToken
$app->getPreAuthCode();//获取预授权码
$app->goAuthPage();//前往授权页；
$app->getAuthCode();//获取授权码;  
$app->refreshMpToken();//刷新授权小程序的AccessToken;
$app->getMpToken();//获取授权小程序的AccessToken;
$app->getMpInfo();//获取授权小程序的信息
```

**注意**
以上各方法根据自己需要调用。事实上，在实际开发中，授权过程调用的方法只需要三个（参照例子写法）：  
一是接收ticket并缓存,需调用`serve()`方法   
二是调用`goAuthPage()`方法，此方法会自动获取令牌(AccessToken)和预授权码(preAuthCode);  
三是在跳转回调页内调用`getMpInfo()`方法

### 模式选择
默认是调试模式，调试模式时每次都会请求AccessToken,生产模式时AccessToken和mpToken会存入数据库，只有生育有效期不到一天时才会重新请求。  
生产模式时，tp_token数据表字段如下；
1. id int(10)   
2. token varchar(50) 
3. create_time int(10)  
4. expires _in int(10)
5. type varchat(50) 可选值：'weixin','baidu','ali','douyin','QQ'
mp_token数据表字段如下：
1. id int(10)   
2. access_token varchar(50)  
3. refresh_token varchar(50)  
4. create_time int(10)  
5. expires _in int(10)
6. type varchat(50) 可选值：'weixin','baidu','ali','douyin','QQ'
### 关于代码提交功能接口的使用说明
代码提交时，需要调用以下三个接口  
1. 代码上传 `$app->uploadCode()`  
此接口需要用户填写三个数据，分别是    
`$ext_json`第三方配置信息  
`user_version`代码版本号    
`user_desc`代码描述  
还有一个字段由系统获取，为`$template_id`模板ID  
2. 提交审核`$app->submitAudit()`  
提交审核后需要等待一天左右，百度会推送审核结果。审核结果的获取，可以参考`https://smartprogram.baidu.com/docs/develop/third/apppage/#%E4%BB%A3%E7%A0%81%E5%AE%A1%E6%A0%B8%E7%8A%B6%E6%80%81%E6%8E%A8%E9%80%81`  
3. 代码发布`$app->relaseCode()`
**在获得授权后，提交代码前，就应该调用修改域名接口`$app->modifyDomain()`来修改小程序的业务域名**



