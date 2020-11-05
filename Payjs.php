<?php

namespace addons\payjs;

use app\common\library\Menu;
use think\Addons;
use think\Request;

/**
 * 插件
 */
class Payjs extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name' => 'payjs',
                'title' => 'PAYJS',
                'icon' => 'fa fa-list-ul',
                'sublist' => [
                    ["name" => "payjs/order/index", "title" => "订单列表"],
                ]
            ]
        ];
        Menu::create($menu);

        //写入默认设置
        $request = Request::instance();
        $notifyUrl = $request->domain() . addon_url('payjs/index/notify');
        $result = set_addon_config('payjs', ['mchid' => '', 'appkey' => '', 'notify_url' => $notifyUrl], true);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete("payjs");
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable("payjs");
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable("payjs");
        return true;
    }

    /**
     * 实现钩子方法
     * @return mixed
     */
    public function appInit()
    {
        //引入公共函数
        require_once 'common.php';
    }


}
