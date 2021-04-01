define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    Fast.config.openArea = ['80%','80%'];
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                showColumns: false,
                showExport: false,
                commonSearch: false,
                extend: {
                    index_url: 'home/link/index' + location.search,
                    add_url: 'home/link/add',
                    edit_url: 'home/link/edit',
                    del_url: 'home/link/del',
                    multi_url: 'home/link/multi',
                    table: 'link',
                }
            });
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "链接标题";};
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        /*{field: 'id', title: __('Id')},*/
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'title', title: __('Title')},
                        {field: 'link', title: __('Link')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'showswitch', title: __('Showswitch'), searchList: {"1":__('Showswitch 1'),"0":__('Showswitch 0')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'topswitch', title: __('Topswitch'), searchList: {"1":__('Topswitch 1'),"0":__('Topswitch 0')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'numbers', title: __('Numbers')},
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