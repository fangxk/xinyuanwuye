define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    Fast.config.openArea = ['80%','80%'];
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'property/activity/activity/index' + location.search,
                    add_url: 'property/activity/activity/add',
                    edit_url: 'property/activity/activity/edit',
                    del_url: 'property/activity/activity/del',
                    multi_url: 'property/activity/activity/multi',
                    table: 'activity',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'title', title: __('Title')},
                        {field: 'desc', title: __('Desc'),visible:false},
                        {field: 'picimage',visible:false,title: __('Picimage'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'number', title: __('Number')},
                        {field: 'num', title: __('Num')},
                        {field: 'starttime',title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'endtime',title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'signtime',title: __('Signtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'statusswitch',title: __('Statusswitch'), searchList: {"0":__('Statusswitch 0'),"1":__('Statusswitch 1')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'province', title: __('Province'),visible:false},
                        {field: 'city', title: __('City'),visible:false},
                        {field: 'dist', title: __('Dist'),visible:false},
                        {field: 'status',visible:false, title: __('Status'), searchList: {"1":__('Status 1'),"0":__('Status 0')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});