# plugin-fastadmin
Fastadmin 对接 PAYJS 插件

## 使用方法

1.下载本代码的zip包，命名为 payjs-1.0.0.zip

2.登录fastadmin后台，点击左侧菜单“插件管理”，点击会员信息，先登录。

3.点击“离线安装”，选择payjs-1.0.0.zip

3.如果提示“请从fastadmin官网下载插件”，需要先注释掉：\vendor\karsonzhang\fastadmin-addons\src\addons\Service.php第161行

```
// 压缩包验证、版本依赖判断
//Service::valid($params);
```

4.填写插件配置信息
