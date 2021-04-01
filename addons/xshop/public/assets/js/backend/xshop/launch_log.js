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
                    index_url: 'xshop/launch_log/index' + location.search,
                    add_url: 'xshop/launch_log/add',
                    edit_url: 'xshop/launch_log/edit',
                    del_url: 'xshop/launch_log/del',
                    multi_url: 'xshop/launch_log/multi',
                    table: 'xshop_launch_log',
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
                        {field: 'platform', title: __('Platform')},
                        {field: 'user.nickname', title: __('User_id')},
                        {field: 'systeminfo', title: __("Systeminfo"), formatter: function(val, row) {
                            try {
                                val = JSON.parse(val.replace(/&quot;/g, '"'));
                                return '<div>操作系统：'  + val.system + '</div>' +
                                        '<div>手机型号：' + val.model + '</div>' +
                                        '<div>手机品牌：' + (val.brand || '-') + '</div>'
                            } catch(e) {
                                return '-'
                            }
                        }},
                        {field: 'ip', title: __("Ip")},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'delete_time', title: __('Delete_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, visible: false}
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