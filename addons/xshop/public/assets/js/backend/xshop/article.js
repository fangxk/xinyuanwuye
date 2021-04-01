define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/xshop/libs/Hook'], function ($, undefined, Backend, Table, Form, Hook) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'xshop/article/index' + location.search,
                    add_url: 'xshop/article/add',
                    edit_url: 'xshop/article/edit',
                    del_url: 'xshop/article/del',
                    multi_url: 'xshop/article/multi',
                    table: 'xshop_article',
                }
            });

            var table = $("#table");

            // 初始化表格
            var config = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title')},
                        {field: 'description', title: __('Description')},
                        {field: 'categories', title: __('文章分类'), formatter: function(val, row, index) {
                            var str = "";
                            for(var i = 0; i < val.length; i ++) {
                                var value = val[i].title;
                                var field = 'category_id';
                                str += '<a style="margin: 0 2px;" href="article?category_id=' + val[i].id +'" class="searchit label label-primary" data-toggle="tooltip" data-field="' + field + '" data-value="' + value + '">' + value + '</a>'
                            }
                            return str;
                        }, searchable: false},
                        {field: 'image', title: __('Image'), formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.toggle},
                        {field: 'sort', title: __('Sort')},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            };
            config = Hook.listen('table_config', config)
            table.bootstrapTable(config);
            // 为表格绑定事件
            Table.api.bindevent(table);
            $('#category-search').change(function() {
                window.location.href = window.location.pathname + '?category_id=' + $(this).val()
            })
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
        select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'xshop/article/index' + location.search,
                    add_url: 'xshop/article/add',
                    edit_url: 'xshop/article/edit',
                    del_url: 'xshop/article/del',
                    multi_url: 'xshop/article/multi',
                    table: 'xshop_article',
                }
            });

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
                        {field: 'title', title: __('Title')},
                        {field: 'description', title: __('Description')},
                        {field: 'categories', title: __('文章分类'), formatter: function(val, row, index) {
                            var str = "";
                            for(var i = 0; i < val.length; i ++) {
                                var value = val[i].title;
                                var field = 'category_id';
                                str += '<a style="margin: 0 2px;" href="article?category_id=' + val[i].id +'" class="searchit label label-primary" data-toggle="tooltip" data-field="' + field + '" data-value="' + value + '">' + value + '</a>'
                            }
                            return str;
                        }, searchable: false},
                        {field: 'jump_url', title: __('Jump_url'), width: '150px'},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.toggle},
                        {field: 'sort', title: __('Sort')},
                        
                        {
                            field: 'operate', title: __('Operate'), events: {
                                'click .btn-chooseone': function (e, value, row, index) {
                                    var multiple = Backend.api.query('multiple');
                                    multiple = multiple == 'true' ? true : false;
                                    Fast.api.close({row: row, multiple: multiple});
                                },
                            }, formatter: function () {
                                return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                            }
                        }
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
            $('#category-search').change(function() {
                window.location.href = window.location.pathname + '?category_id=' + $(this).val()
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});