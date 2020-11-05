# plugin-fastadmin
Fastadmin 对接 PAYJS 插件

### 安装方法

1.下载本代码的zip包，命名为 payjs-1.0.0.zip

2.登录fastadmin后台，点击左侧菜单“插件管理”，点击会员信息，先登录。

3.点击“离线安装”，选择payjs-1.0.0.zip

3.如果提示“请从fastadmin官网下载插件”，需要先注释掉：\vendor\karsonzhang\fastadmin-addons\src\addons\Service.php第161行

```
// 压缩包验证、版本依赖判断
//Service::valid($params);
```

4.填写插件配置信息

### 使用方法

指定订单金额：
http://yourname/addons/payjs/index?total_fee=0.01

指定订单号：
http://yourname/addons/payjs/index?total_fee=0.01&out_trade_no=123456

指定订单标题：
http://yourname/addons/payjs/index?total_fee=0.01&subject=测试

指定支付通道：
http://yourname/addons/payjs/index?total_fee=0.01&pay_channel=weixin

指定使用JSAPI支付
http://yourname/addons/payjs/index?total_fee=0.01&pay_mode=jsapi

指定使用收银台支付
http://yourname/addons/payjs/index?total_fee=0.01&pay_mode=cashier

全都指定：
http://yourname/addons/payjs/index?out_trade_no=123456&total_fee=0.01&subject=测试&pay_channel=weixin

### 异步通知

异步通知在/addons/payjs/notify中

### 退款

退款请参考OrderService中的refund方法
