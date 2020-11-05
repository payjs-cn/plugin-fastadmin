<?php

namespace app\admin\controller;

use addons\payjs\library\OrderService;
use app\common\controller\Backend;

/**
 * 订单列表
 * Class Index
 * @package app\admin\controller\payjs
 */
class Payjs extends Backend
{

    /**
     * @var \app\admin\model\PayjsOrder
     */
    protected $model = null;
    protected $searchFields = 'out_trade_no,outer_tid,transaction_tid';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('PayjsOrder');
    }

    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            $money = $this->model
                ->where($where)
                ->order($sort, $order)
                ->sum('total_fee');

            $result = array("total" => $list->total(), "rows" => $list->items(),"money"=>$money);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 退款
     */
    public function refund()
    {
        if($this->request->isPost()){
            $order = $this->model->find($this->request->post('id'));
            if(!$order){
                $this->error('未找到该订单');
            }
            if($order->status!=0){
                $this->error('订单当前状态不支持退款');
            }
            if($this->request->post('total_fee')<0.01){
                $this->error('退款金额需大于等于0.01元');
            }
            if($this->request->post('total_fee')>$order->total_fee){
                $this->error('退款金额不能超过订单金额');
            }
            $result = OrderService::refund($order->out_trade_no);
            if($result['status']!='success'){
                $this->error($result['msg']);
            }else{
                $this->success($result['msg']);exit();
            }
        }
        $id = intval($this->request->param('ids'));
        $order = $this->model->find($id);
        $data['out_trade_no'] = $order->out_trade_no;
        $data['total_fee'] = $order->total_fee;
        $data['id'] = $order->id;
        $this->view->assign($data);
        return $this->view->fetch();
    }

    /**
     * 详情
     */
    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }
}
