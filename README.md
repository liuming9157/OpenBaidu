### 百度小程序第三方平台SDK  

#### 注意：本项目仍在开发中，无法用于生产环境，欢迎共同开发

 
### 使用方法
参照`exmaple\Index.php`中写法

### 项目结构
初始的目录结构如下：

~~~
OpenBaidu  
├─src           应用目录
│  ├─Application        核心类库
│  ├─library            模块目录
│  │  └─Miniapp         授权小程序
│  └─util               工具目录
│
├─example               示例目录（对外访问目录）
   └─index.php          示例写法

~~~

### 方法介绍  
```
protected $config=[
    'encodingAesKey'=>'',//设置aesKey
	'client_id'=>'',//设置第三方平台secret
	'redirect_uri'=>'',//设置授权后回调URI
	'debug'=>true//生产模式时请设置为false,并且按照文档添加数据库
     ];

$app=new Application($this->config);
$app->serve();接收服务器推送时返回success，并缓存
$app->ticket();解密获取ticket
$app->tpToken();//获取第三方平台的AccessToken并缓存
$app->preAuthCode();获取预授权码
$app->jumpToAuth();//前往授权页；
$app->authCode();//获取授权码，一般在回调页调用
$app->refreshToken();//刷新授权小程序的AccessToken;
$app->mpToken();//获取授权小程序的AccessToken;存入数据库
$app->mpInfo();//获取授权小程序的信息
```

**注意**
以上各方法根据自己需要调用。事实上，在实际开发中，真正需要开发者调用的方法只需要san个（参照例子写法）：  
一是接收ticket并缓存,需调用`serve()`方法      
二是用户点击授权按钮时，跳转到授权页，此时调用`jumpToAuthPage()`方法;   
三是在授权回调页获取小程序aceess_token并保存，此时在跳转回调页内调用`mpToken()`方法，  
`注意：此方法返回的不是access_token，而是一个数组，该数组包含了access_token/refresh_token/expires_in`，建议存入数据库    

### 对access_token的处理
1. 开发者一定要注意，第三方平台有一个access_token,授权小程序也有一个access_token,这两个access_token不一样，为了区分，代码中一般用tpToken表示第三方平台令牌，用mpToken表示小程序令牌。
2. tpToken有效期为1个月，mpToken有效期为1小时。tpToken本框架已经做了处理，开发者不需关注，建议开发者把mpToken令牌都存入数据库，每次调用时先从数据库查询，而不是从百度和微信服务器获取。  
3. mpToken是否过期需要大家自行判断，如果过期使用refreshToken刷新  
以下是建议的数据表设计：  
  


mp_token数据表字段如下：
1. id int(10)   
2. access_token varchar(150)  
3. refresh_token varchar(150)  
4. update_time int(10)  
5. expires _in int(10)
6. type varchar(50) 可选值：'weixin','baidu','ali','douyin','QQ'  
7. appid varchar(50)
8. app_name  varchar(100)
9. photo_addr varchar(500)

### 授权后获取实例

`$miniapp=$this->app->miniapp($token)`

### 关于代码提交功能接口的使用说明
代码提交时，需要调用以下三个接口  
1. 代码上传 `$mimiapp->uploadCode()`  
此接口需要用户填写三个数据，分别是    
`$ext_json`第三方配置信息  
`user_version`代码版本号    
`user_desc`代码描述  
还有一个字段由系统获取，为`$template_id`模板ID  
2. 提交审核`$miniapp->submitAudit()`  
提交审核后需要等待一天左右，百度会推送审核结果。审核结果的获取，可以参考`https://smartprogram.baidu.com/docs/develop/third/apppage/#%E4%BB%A3%E7%A0%81%E5%AE%A1%E6%A0%B8%E7%8A%B6%E6%80%81%E6%8E%A8%E9%80%81`  
3. 代码发布`$app->relaseCode()`  
**在获得授权后，提交代码前，就应该调用修改域名接口`$app->modifyDomain()`来修改小程序的业务域名**



