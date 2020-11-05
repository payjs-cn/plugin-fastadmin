<?php

namespace app\admin\model;

use app\common\model\MoneyLog;
use app\common\model\ScoreLog;
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


    public function getOriginData()
    {
        return $this->origin;
    }
}
