define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'payjs/index',
                    add_url: '',
                    edit_url: '',
                    del_url: 'payjs/del',
                    multi_url: 'payjs/multi',
                }
            });

            var table = $("#table");

            //当表格数据加载完成时
            table.on('load-success.bs.table', function (e, data) {
                $("#money").text(data.money);
                $("#total").text(data.total);
            });


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                searchFormVisible: true,
                //启用固定列
                fixedColumns: true,
                //固定右侧列数
                fixedRightNumber: 1,
                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'id', title: 'ID', operate: false},
                        {
                            field: 'type',
                            title: '支付方式',
                            searchList: {"alipay": '支付宝', "weixin": '微信支付'},
                            operate: 'FIND_IN_SET',
                            custom: {'alipay': 'info', 'weixin': 'success'},
                            formatter: Table.api.formatter.label
                        },
                        {field: 'out_trade_no', title: '订单号', operate: 'LIKE %...%', placeholder: '模糊搜索'},
                        {field: 'subject', title: '订单标题', operate: false},
                        {
                            field: 'outer_tid',
                            title: 'Payjs订单号',
                            operate: 'LIKE %...%',
                            placeholder: '模糊搜索',
                            visible: false
                        },
                        {
                            field: 'transaction_tid',
                            title: '支付流水号',
                            operate: 'LIKE %...%',
                            placeholder: '模糊搜索',
                            visible: false
                        },
                        {field: 'total_fee', title: '订单金额', operate: 'RANGE'},
                        {
                            field: 'status',
                            title: '订单状态',
                            searchList: {"0": '已支付', "1": '待支付', "2": '已退款'},
                            operate: 'FIND_IN_SET',
                            custom: {1: 'gray', 2: 'danger', 0: 'success'},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'pay_at',
                            title: '支付时间',
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true
                        },
                        {
                            field: 'created_at',
                            title: __('Create time'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true
                        },
                        {
                            field: 'operate', width: "130px", title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                url: 'payjs/detail',
                                visible: function (row) {
                                    //返回true时按钮显示,返回false隐藏
                                    return true;
                                }
                            },{
                                name: 'del',
                                text: __('del'),
                                icon: 'fa fa-trash',
                                classname: 'btn btn-danger btn-xs btn-delone',
                                visible: function (row) {
                                    //返回true时按钮显示,返回false隐藏
                                    return row.status==1 ? true : false;
                                }
                            },{
                                name: 'refund',
                                text: '退款',
                                icon: 'fa fa-undo',
                                classname: 'btn btn-warning btn-xs btn-dialog',
                                url: 'payjs/refund',
                                visible: function (row) {
                                    //返回true时按钮显示,返回false隐藏
                                    return row.status==0 ? true : false;
                                }
                            }],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
        }
    };
    return Controller;
});
