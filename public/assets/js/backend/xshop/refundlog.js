define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/xshop/libs/Hook'], function ($, undefined, Backend, Table, Form, Hook) {

    var Controller = {
        index: function() {
            Hook.init(function(a) {
                console.log(a, 2)
                Controller.bootIndex()
            })
        },
        bootIndex: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'xshop/refundlog/index' + location.search,
                    add_url: 'xshop/refundlog/add',
                    edit_url: 'xshop/refundlog/edit',
                    del_url: 'xshop/refundlog/del',
                    multi_url: 'xshop/refundlog/multi',
                    table: 'xshop_refundlog',
                }
            });

            var table = $("#table");

            var config = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'out_trade_no', title: __('Out_trade_no'), formatter: function(val, index, row) {
                            var sn = val.split('RE')[0]
                            return '<a class="btn-dialog" title="查看订单" href="xshop/order/index?order_sn=' + sn + '">' + val + '</a>'
                        }},
                        {field: 'out_refund_no', title: __('Out_refund_no')},
                        {field: 'total_fee', title: __('Total_fee'), operate:'BETWEEN'},
                        {field: 'refund_fee', title: __('Refund_fee'), operate:'BETWEEN'},
                        {field: 'pay_type', title: __('Pay_type')},
                        {field: 'status', title: __('Status'), formatter: function(val, index, row) {
                            return val == 1 ? '已退款' : '未退款'
                        }},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            }
            config = Hook.listen('table_config', config)
            // 初始化表格
            table.bootstrapTable(config);

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('post-body.bs.table', function() {
                var payload = {
                    e: this,
                    table: table
                }
                Hook.listen('table_event', payload)
            })
            Hook.listen('document', table)
        },
        add: function () {
            Controller.api.bindevent();
            Hook.listen('form')
        },
        edit: function () {
            Controller.api.bindevent();
            Hook.listen('form')
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});