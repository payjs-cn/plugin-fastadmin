<?php

namespace addons\payjs\model;

use think\Model;

class PayjsOrder extends Model
{
    // 表名
    protected $name = 'payjs_orders';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'timestamp';
    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /**
     * 设置payjs平台订单号
     * @param $outTradeNo
     * @param int $status
     */
    public static function setPayjsOrderId(string $outTradeNo, string $orderId)
    {
        return self::where(['out_trade_no' => $outTradeNo])->update([
            'outer_tid' => $orderId
        ]);
    }


    /**
     * 获取payjs平台订单号
     * @param string $outTradeNo
     * @return array|bool|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getPayjsOrderId(string $outTradeNo)
    {
        return self::where(['out_trade_no' => $outTradeNo])->find();
    }

    /**
     * 修改订单状态
     * @param string $outTradeNo
     * @param int $status
     * @return PayjsOrder
     */
    public static function changeOrderStatus(string $outTradeNo, int $status = 1)
    {
        return self::where(['out_trade_no' => $outTradeNo])->update([
            'status' => intval($status)
        ]);
    }

}
