<?php

namespace addons\payjs\controller;

use addons\payjs\library\OrderService;
use addons\payjs\library\PayjsService;
use think\addons\Controller;
use think\Log;
use think\Request;

class Index extends Controller
{

    public function index()
    {
        $config = get_addon_config('payjs');
        $data['out_trade_no'] = input('out_trade_no') ?: generateOutTradeNo();
        $data['total_fee'] = input('total_fee') ? sprintf("%.2f", floatval(input('total_fee'))) : 0.01;
        $data['subject'] = input('subject') ?: '订单号：' . $data['out_trade_no'];
        $data['pay_channel'] = input('pay_channel') ?: $config['pay_channel'];
        $data['pay_mode'] = input('pay_mode') ?: 'native';
        if ($data['pay_mode'] == 'jsapi') return $this->jsapi($data);
        if ($data['pay_mode'] == 'cashier') return $this->cashier($data);
        return $this->view->fetch('index',$data);
    }

    /**
     * 获取支付二维码
     */
    public function getQrcode()
    {
        $config = get_addon_config('payjs');
        $data['out_trade_no'] = input('out_trade_no') ?: generateOutTradeNo();
        $data['total_fee'] = input('total_fee') ? sprintf("%.2f", floatval(input('total_fee'))) : 0.01;
        $data['subject'] = input('subject') ?: '订单号：' . $data['out_trade_no'];
        $data['pay_channel'] = $config['pay_channel'] ?: 'all';
        $data['type'] = input('paymode') ?: 'weixin';
        //添加数据库订单
        OrderService::create($data);
        //获取支付二维码
        $payjsServeice = new PayjsService($config);
        $result = $payjsServeice->getQrcode($data);
        $arr = json_decode($result, true);
        if ($arr['return_code'] == 1) {
            //设置payjs平台订单号
            OrderService::setPayjsOrderId($arr['out_trade_no'], $arr['payjs_order_id']);
        }
        echo $result;
        exit();
    }

    /**
     * jspai支付
     * @param $data
     */
    public function jsapi($data)
    {
        $config = get_addon_config('payjs');
        $payjsServeice = new PayjsService($config);
        //获取openid
        $data['openid'] = $payjsServeice->getOpenid();
        //获取jsapi参数
        $jsapiConfig = $payjsServeice->jsapi($data);
        //添加数据库订单
        $data['outer_tid'] = $jsapiConfig['outer_tid'];
        OrderService::create($data);
        $this->view->assign($jsapiConfig);
        return $this->view->fetch('index/jsapi');
    }

    /**
     * 收银台支付
     * @param $data
     */
    public function cashier($data)
    {
        $config = get_addon_config('payjs');
        $payjsServeice = new PayjsService($config);
        //收银台支付
        $url = $payjsServeice->cashier($data);
        if (in_array($data['pay_channel'],['all','alipay']) && !isAlipay() && !isWeixin()) {
            $url = "alipays://platformapi/startapp?appId=20000067&url=" . urlencode($url);
        }
        header("Location:{$url}");
        exit();
    }

    /**
     * 查询订单支付状态
     */
    public function checkOrder()
    {
        $data = OrderService::orderQuery(input('out_trade_no'));
        return json($data);
    }

    /**
     * 支付结果显示页面
     */
    public function response()
    {
        $outerTid = OrderService::getPayjsOrderId(input('out_trade_no'));
        if (!$outerTid) exit('支付失败：未找到该笔订单');
        $config = get_addon_config('payjs');
        $payjsServeice = new PayjsService($config);
        $result = $payjsServeice->orderquery($outerTid);
        $data = json_decode($result, true);
        if (isset($data['message'])) {
            exit('支付失败：' . $data['message']);
        }
        if ($data['return_code'] == 0) {
            exit('支付失败：' . $data['msg']);
        }
        return $this->view->fetch('index/response',$data);
    }

    /**
     * 异步通知
     */
    public function notify()
    {
        $request = Request::instance();
//        Log::debug($request->post());
        $config = get_addon_config('payjs');
        $payjsServeice = new PayjsService($config);
        $result = $payjsServeice->check($request->post());
        if ($result === false) {
            exit('sign error');
        }
        $order = OrderService::getOrderByTid($request->post('payjs_order_id'));
        if(!$order){
            echo 'order is not exist';
            exit();
        }
        if ($order->status == 1) {
            $data = [
                'status' => 0,
                'transaction_tid' => $request->post('transaction_id'),
                'pay_at' => $request->post('time_end'),
                'buyer_info' => $request->post('openid'),
            ];
            OrderService::updateOrderByTid($request->post('payjs_order_id'), $data);
        }
        echo 'success';
        exit();
    }
}
