define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/xshop/libs/Hook'], function ($, undefined, Backend, Table, Form, Hook) {

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
                    index_url: 'xshop/order_products/index' + location.search,
                    add_url: 'xshop/order_products/add',
                    edit_url: 'xshop/order_products/edit',
                    del_url: 'xshop/order_products/del',
                    multi_url: 'xshop/order_products/multi',
                    table: 'xshop_order_product',
                }
            });

            var table = $("#table");

            var config = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {field: 'id', title: __('Id')},
                        {field: 'order_id', title: __('Order_id'), visible: false},
                        {field: 'product_id', title: __('Product_id'), visible: false},
                        {field: 'sku_id', title: __('Sku_id'), visible: false},
                        {field: 'shop_id', title: __('Shop_id'), visible: false},
                        {field: 'title', title: __('Title'), cellStyle: function() {return {css: {'max-width': '240px', display: '', overflow: 'hidden', 'text-overflow': 'ellipsis'}}}},
                        {field: 'description', title: __('Description'), cellStyle: function() {return {css: {'max-width': '240px', display: '', overflow: 'hidden', 'text-overflow': 'ellipsis'}}}},
                        {field: 'image', title: __('Image'), events: Table.api.events.images, formatter: Table.api.formatter.images},
                        {field: 'attributes', title: __('Attributes')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'quantity', title: __('Quantity')},
                        {field: 'product_price', title: __('Product_price'), operate:'BETWEEN'},
                        {field: 'discount_price', title: __('Discount_price'), operate:'BETWEEN', visible: false},
                        {field: 'order_price', title: __('Order_price'), operate:'BETWEEN', visible: false},
                        {field: 'buyer_review', title: __('Buyer_review'), visible: false},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, visible: false},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, visible: false},
                        {field: 'delete_time', title: __('Delete_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, visible: false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate, visible: false}
                    ]
                ]
            }
            config = Hook.listen('table_config', config)
            // 初始化表格
            table.bootstrapTable(config);

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('post-body.bs.table', function(e, setting, json, xhr) {
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