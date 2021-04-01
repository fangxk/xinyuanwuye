
define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/xshop/libs/CustomFormatter', 'backend/xshop/libs/Hook'], function ($, undefined, Backend, Table, Form, Formatter, Hook) {
    var Controller = {
        index: function() {
            Hook.init(function() {
                Controller.bootIndex()
            })
        },
        bootIndex: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'xshop/order/index' + location.search,
                    add_url: 'xshop/order/add',
                    edit_url: 'xshop/order/edit',
                    del_url: 'xshop/order/del',
                    multi_url: 'xshop/order/multi',
                    table: 'xshop_order',
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
                        {field: 'order_sn', title: __('Order_sn'), formatter: function(val, row, index) {
                            return  val + '<br>'
                                    + Table.api.formatter.datetime.call(this, row.create_time, row, index)
                            
                        }},
                        {field: 'is_pay', title: __('Is_pay'), formatter: function(val, row, index) {
                            var that = this
                            var h = function(text) {
                                return Formatter.search.call(that, val, row, index, text)
                            }

                            if (val == 0) return h('未付款')
                            else {
                                var payTypeArr = {
                                    wechat: '微信支付', alipay: '支付宝'
                                }
                                var payMethodArr = {
                                    mp: '公众号', miniapp: '小程序', tt: '字节跳动', wap: 'wap'
                                }
                                return '<div style="color: green;">' + h('已付款') +'</div><div><span>' + (payTypeArr[row.pay_type] || '') + '</span>&nbsp;&nbsp;<span>' + (payMethodArr[row.pay_method] || '') + '</span></div><div>' + row.pay_time_text + '</div>'
                            }
                        }},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, visible: false},
                        {field: 'is_delivery', title: __('Is_delivery'), formatter: function(val, row, index) {
                            if (val == 0) return Formatter.search.call(this,val, row, index, '未发货')
                            else {
                                return '<div>' + row.express.name + '</div><div>' + row.express_no + '</div><div>' + row.delivery_text + '</div>'
                            }
                        }},
                        {field: 'delivery', title: __('Delivery'), addclass:'datetimerange', formatter: Table.api.formatter.datetime, visible: false},
                        {field: 'status', title: __('Status'), formatter: function(val, row) {
                                var tip = ''
                                if (val == -1) tip += '<div style="color: red;">已取消</div>'
                                else tip += '<div style="color: green;">正常</div>'

                                var groupon_status = {
                                    0: '拼团中',
                                    1: '拼团成功',
                                    2: '拼团失败,待退款',
                                    3: '拼团失败,已退款',
                                    '-1': '拼团取消'
                                }

                                if (row.order_type == 1) {
                                    tip += '<a class="btn-dialog" title="查看团队成员" href="xshopgroupon/Groupmembers/index?member_order_sn=' + row.order_sn + '" data-value="' + row.groupon_status + '">【' + groupon_status[row.groupon_status] + '】</a>'
                                }

                                var after_status = {
                                    1: '申请退款',
                                    2: '已退款',
                                    '-1': '已取消退款',
                                    '-2': '已拒绝退款'
                                }
                                if (after_status[row.after_sale_status]) {
                                    var str = after_status[row.after_sale_status]
                                    if (row.after_sale_status == 2) str += row.refund_fee + '元'
                                    tip += '<div>' + str + '</div><div>客户：' + row.after_buyer_remark + '</div><div>商家：' + row.after_saler_remark + '</div>'
                                }
                                return tip
                        }},
                        {field: 'contactor', title: __('Contactor'), formatter: function(val, row) {
                            return '<div>' + val +'&nbsp;&nbsp;' + row.contactor_phone + '</div><div>' + row.address + '</div><div>备注：' + row.remark + '</div>'
                        }},
                        {field: 'contactor_phone', title: __('Contactor_phone'), visible: false},
                        {field: 'price', title: '价格', formatter: function(val, row, index) {
                            return  '商品:' + row.products_price + ';'
                                    + __('Delivery_price') + ':' + row.delivery_price + '<br>'
                                    + __('优惠') + ':' + row.discount_price + '<br>'
                                    + __('小计') + ':' + row.order_price + ';'
                                    + __('已付') + ':' + row.payed_price
                        }},
                        {field: 'delivery_price', title: __('Delivery_price'), operate:'BETWEEN', visible: false},
                        {field: 'order_price', title: __('Order_price'), operate:'BETWEEN', visible: false},
                        {field: 'products_price', title: __('Products_price'), operate:'BETWEEN', visible: false},
                        {field: 'discount_price', title: __('Discount_price'), operate:'BETWEEN', visible: false},
                        {field: 'payed_price', title: __('Payed_price'), operate:'BETWEEN', visible: false},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, visible: false},
                        {field: 'user.username', title: __('User.username'), formatter: function(val, row) {
                            return '<div>' + val + '</div><div>'  + row.user.nickname + '</div>'
                        }},
                        {field: 'order_type', title: __("订单类型"), formatter: Formatter.search, source: {
                            0: {
                                text: '普通订单'
                            },
                            1: {
                                text: '团购订单'
                            }
                        }},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate, buttons: [
                            {
                                name: 'detail', text: '查看', icon: '', classname: 'btn btn-xs btn-success btn-dialog',
                                dropdown: '操作',
                                url: function(row) {
                                    return 'xshop/order_products/index?order_id=' + row.id;
                                }
                            },
                            {
                                name: 'refund-auto', text: '原路退款', icon: '',classname: 'btn btn-xs btn-info btn-refund-auto btn-refund',
                                dropdown: '操作',
                                extend: 'data-refund="1"',
                                visible: function(row) {
                                    if (row.is_pay == 1 && row.refund_fee < row.order_price && (row.after_sale_status == 1 || row.groupon_status == 1) && ['alipay', 'wechat'].indexOf(row.pay_type) > -1) return true
                                    return false
                                }
                            },
                            {
                                name: 'refund-hand', text: '手动退款', classname: 'btn btn-xs btn-info btn-refund-hand btn-refund',
                                dropdown: '操作',
                                extend: 'data-refund="2"',
                                visible: function(row) {
                                    if (row.is_pay == 1 && row.refund_fee < row.order_price && (row.after_sale_status == 1 || row.groupon_status == 1) == 1) return true
                                    else return false
                                }
                            },
                            {
                                name: 'after-sale-reject', text: '拒绝退款', classname: 'btn btn-xs btn-info btn-after-reject',
                                dropdown: '操作',
                                visible: function(row) {
                                    if (row.is_pay == 1 && row.after_sale_status == 1) return true
                                    return false
                                }
                            },
                            {
                                name: 'pay', text: '付款', icon: '', classname: 'btn btn-xs btn-success btn-pay',
                                dropdown: '操作',
                                visible: function(row) {
                                    if (row.status != -1 && row.is_pay == 0) return true
                                    return false
                                }
                            }, {
                                name: 'ship', text: '发货', icon: '', classname: 'btn btn-xs btn-info btn-ship',
                                dropdown: '操作',
                                visible: function(row) {
                                    if (row.status != -1 && row.is_delivery == 0) return true
                                    return false
                                }
                            }
                        ]}
                    ]
                ]
            };
            config = Hook.listen('table_config', config)
            // 初始化表格
            table.bootstrapTable(config);

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('post-body.bs.table', function(e, setting, json, xhr) {
                $('.btn-pay', this).on('click', function(e) {
                    var row = table.bootstrapTable('getData')[$(this).data('row-index')]
                    Layer.confirm('确定付款吗？', {icon: 3, title: '提示'}, function(index) {
                        Fast.api.ajax({
                            url: 'xshop/order/pay',
                            data: {
                                id: row.id
                            }
                        }, function(data, ret) {
                            table.bootstrapTable('refresh')
                        })
                        Layer.close(index)
                    })
                })

                $('.btn-ship', this).on('click', function(e) {
                    var row = table.bootstrapTable('getData')[$(this).data('row-index')]
                    if (!window.express) {
                        Fast.api.ajax({
                            url: 'xshop/express/getAll',
                        }, function(data, ret) {
                            window.express = data
                            Controller.api.shipFunc(row.id)
                            return false
                        }, function(data, ret) {
                            return false
                        })
                    } else {
                        Controller.api.shipFunc(row.id)
                    }
                })

                $('.btn-refund', this).on('click', function(e) {
                    var refund_type = $(this).data('refund')
                    var title = refund_type == 1 ? '原路退款' : '手工退款'
                    var row = table.bootstrapTable('getData')[$(this).data('row-index')]
                    var content = '<div class="form" style="padding: 5px;">' + 
                                        '<div class="form-group"><label class="label-control col-xs-12 col-sm-12">退款金额</label><input class="form-control" id="refund-price" value="' + (row.payed_price - row.refund_fee) + '"/></div>' + 
                                        '<div class="form-group"><label class="label-control col-xs-12 col-sm-12">备注</label><input class="form-control" id="remark" /></div>' +
                                        '<div class="form-group"><label class="label-control col-xs-12 col-sm-12">是否关闭订单</label>' + 
                                            '<select id="close_order" class="form-control"><option value="1">关闭</option><option value="0">不关闭</option></select>' + 
                                        '</div>' + 
                                    '</div>';
                    var index = Layer.open({
                        type: 1,
                        title: title,
                        area: ['350px', '330px'],
                        content: content,
                        btn: ['确定', '取消'],
                        btn1: function() {
                            var form = {
                                order_sn: row.order_sn,
                                price: $("#refund-price").val(),
                                after_saler_remark: $("#remark").val(),
                                close_order: $("#close_order").val()
                            }
                            Fast.api.ajax({
                                url: 'xshop/order/refund?type=' + refund_type,
                                data: form
                            }, function(ret, data) {
                                Layer.close(index)
                                table.bootstrapTable('refresh')
                                return false
                            }, function(ret, data) {
                                Toastr.error(data.msg)
                                return false
                            })
                        }
                    })
                })

                $('.btn-after-reject', this).on('click', function(e) {
                    var row = table.bootstrapTable('getData')[$(this).data('row-index')]
                    var content = '<div class="form" style="padding: 5px;"><div class="form-group"><label class="label-control col-xs-12 col-sm-12">拒绝退款原因</label><input class="form-control" id="reject-reason" value=""/></div></div>';
                    var index = Layer.open({
                        type: 1,
                        title: '拒绝退款',
                        area: ['350px', '230px'],
                        content: content,
                        btn: ['确定', '取消'],
                        btn1: function() {
                            var form = {
                                order_sn: row.order_sn,
                                after_saler_remark: $("#reject-reason").val()
                            }
                            Fast.api.ajax({
                                url: 'xshop/order/reject',
                                data: form
                            }, function(ret, data) {
                                Layer.close(index)
                                table.bootstrapTable('refresh')
                                return false
                            }, function(ret, data) {
                                Toastr.error(data.msg)
                                return false
                            })
                        }
                    })
                })
                
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
            },
            shipFunc: function(id) {
                var html = '<select class="form-control" id="express_code">'
                for (var i = 0; i < window.express.length; i ++) {
                    var item = window.express[i]
                    html += '<option value="' + item.code + '">' + item.name + '</option>'
                }
                html += '</select>'
                html += '<div class="form-group"><label class="col-xs-12 col-xs-12 label-control">快递单号</label><input class="form-control" id="express_no" /></div>'
                var content = '<div class="form" style="padding: 5px;"><div class="form-group"><label class="label-control col-xs-12 col-sm-12">快递公司</label>' + html + '</div></div>';
                var index = Layer.open({
                    type: 1,
                    title: '发货',
                    area: ['350px', '230px'],
                    content: content,
                    btn: ['确定', '取消'],
                    btn1: function() {
                        var express_code = $("#express_code").val()
                        var express_no = $('#express_no').val()
                        Fast.api.ajax({
                            url: 'xshop/order/ship',
                            data: {
                                id: id,
                                express_code: express_code,
                                express_no: express_no
                            }
                        }, function(data, ret) {
                        }, function(data, ret) {
                        })
                        Layer.close(index)
                    }
                })
            }
        }
    };
    return Controller;
});