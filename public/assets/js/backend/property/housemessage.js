define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                /*showColumns: false,*/
                showExport: false,
                /*commonSearch: false,*/
                extend: {
                    index_url: 'property/housemessage/index' + location.search,
                    add_url: 'property/housemessage/add',
                    edit_url: 'property/housemessage/edit',
                    del_url: 'property/housemessage/del',
                    multi_url: 'property/housemessage/multi',
                    table: 'housemessage',
                }
            });
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "管家名称";};
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_name', title: __('User_name'),operate: 'LIKE'},
                        {field: 'user_mobile', title: __('User_mobile'),operate: 'LIKE'},
                        {field: 'homename', title: __('Homename'),operate: 'LIKE'},
                        {field: 'homemobile', title: __('Homemobile'),operate: 'LIKE'},
                        {field: 'satisfied', title: __('Satisfied')},
                        {field: 'reson', title: __('Reson')},
                        {field: 'evaluatetext', title: __('Evaluatetext')},
                        {field: 'project_name', title: __('Project_name'),operate: 'LIKE'},
                        {field: 'house_name', title: __('House_name'),operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'lookswitch', title: __('Lookswitch'), searchList: {"0":__('Lookswitch 0'),"1":__('Lookswitch 1')}, table: table, formatter: Table.api.formatter.toggle},
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