define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/xshop/libs/Hook'], function ($, undefined, Backend, Table, Form, Hook) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'xshop/articlecategory/index' + location.search,
                    add_url: 'xshop/articlecategory/add',
                    edit_url: 'xshop/articlecategory/edit',
                    del_url: 'xshop/articlecategory/del',
                    multi_url: 'xshop/articlecategory/multi',
                    table: 'xshop_article_category',
                }
            });

            var table = $("#table");

            // 初始化表格
            var config = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                escape: false,
                pagination: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), align: 'left'},
                        {field: 'description', title: __('Description')},
                        {field: 'sort', title: __('Sort')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            };
            config = Hook.listen('table_config', config)
            table.bootstrapTable(config)
            // 为表格绑定事件
            Table.api.bindevent(table);
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