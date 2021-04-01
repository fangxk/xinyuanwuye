define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/xshop/libs/Hook', 'backend/xshop/libs/CustomFormatter'], function ($, undefined, Backend, Table, Form, Hook, CustomFormatter) {

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
                    index_url: 'xshop/template/index' + location.search,
                    add_url: 'xshop/template/add',
                    edit_url: 'xshop/template/edit',
                    del_url: 'xshop/template/del',
                    multi_url: 'xshop/template/multi',
                    table: 'xshop_template',
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
                        {field: 'code', title: __('Code')},
                        {field: 'description', title: __('Description')},
                        {field: 'content', title: __('Content'), operate:'BETWEEN', visible: false},
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
                var form = $("form[role=form]");
                Form.api.bindevent(form);
                CustomFormatter.bindEvent(form);    
            }
        }
    };
    return Controller;
});