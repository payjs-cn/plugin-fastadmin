<?php

namespace addons\payjs\library;


use addons\payjs\model\PayjsOrder;

class OrderService
{
    public static function create($data)
    {
        unset($data['pay_channel']);
        unset($data['openid']);
        unset($data['pay_mode']);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return PayjsOrder::create($data);
    }

    /**
     * 设置payjs平台订单号
     * @param string $outTradeNo
     * @param string $orderId
     * @return PayjsOrder
     */
    public static function setPayjsOrderId(string $outTradeNo, string $orderId)
    {
        return PayjsOrder::setPayjsOrderId($outTradeNo,$orderId);
    }


    /**
     * 获取payjs平台订单号
     * @param string $outTradeNo
     * @return mixed
     */
    public static function getPayjsOrderId(string $outTradeNo)
    {
        return PayjsOrder::where(['out_trade_no' => $outTradeNo])->value('outer_tid');
    }

    /**
     * 订单状态查询
     * @param string $outTradeNo 商户订单号
     */
    public static function orderQuery(string $outTradeNo)
    {
        $data['status'] = 'error';
        $data['code'] = 2;      //未支付状态
        $order = PayjsOrder::getPayjsOrderId($outTradeNo);
        if (!$order->outer_tid) {
            $data['msg'] = '未找到该笔订单';
            return $data;
        }
        $config = get_addon_config('payjs');
        $payjsServeice = new PayjsService($config);
        $result = $payjsServeice->orderquery($order->outer_tid);
        $result = json_decode($result, true);
        if ($result['return_code'] == 1) {
            //查询成功
            $data['status'] = 'success';
            if ($result['status'] == 1 && $order->status == 1) {
                PayjsOrder::changeOrderStatus($outTradeNo, 0);
            }
            if ($result['status'] == 1) $data['code'] = 0;     //修改为已支付状态
            return $data;
        } else {
            $data['msg'] = $result['return_msg'];
            return $data;
        }
    }

    /**
     * 根据payjs订单号查询订单记录
     * @param string $tid
     * @return mixed
     */
    public static function getOrderByTid(string $tid)
    {
        return PayjsOrder::where(['outer_tid' => $tid])->find();
    }

    /**
     * 根据payjs订单号修改订单记录
     * @param $outTradeNo
     * @param int $status
     */
    public static function updateOrderByTid(string $tid, $data)
    {
        return PayjsOrder::where(['outer_tid' => $tid])->update($data);
    }

    /**
     * 退款
     * @param string $outTradeNo 商户订单号
     * @return mixed
     */
    public static function refund(string $outTradeNo)
    {
        $data['status'] = 'error';
        $order = PayjsOrder::where(['out_trade_no' => $outTradeNo])->find();
        if ($order->status != 0) {
            $data['msg'] = '该订单状态不能退款';
            return $data;
        }
        $config = get_addon_config('payjs');
        $payjsServeice = new PayjsService($config);
        $result = $payjsServeice->refund($order->outer_tid);
        $result = json_decode($result, true);
        if ($result['return_code'] == 1) {
            //退款成功，修改订单状态
            PayjsOrder::changeOrderStatus($outTradeNo, 2);
            $data['status'] = 'success';
            $data['msg'] = '退款成功';
            return $data;
        } else {
            $data['msg'] = $result['return_msg'];
            return $data;
        }
    }
}
