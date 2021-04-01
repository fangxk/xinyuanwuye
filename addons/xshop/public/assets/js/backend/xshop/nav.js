define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/xshop/libs/CustomFormatter', 'backend/xshop/libs/Hook', 'template'], 
    function ($, undefined, Backend, Table, Form, Formatter, Hook, Template) {
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
                    index_url: 'xshop/nav/index' + location.search,
                    add_url: 'xshop/nav/add',
                    edit_url: 'xshop/nav/edit',
                    del_url: 'xshop/nav/del',
                    multi_url: 'xshop/nav/multi',
                    table: 'xshop_nav',
                }
            });

            var table = $("#table");

            var config = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'sort DESC,id DESC',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title')},
                        {field: 'description', title: __('Description')},
                        {field: 'image', title: __('Image'), formatter: Table.api.formatter.image, searchable: false},
                        {field: 'nav_type', title: __('Nav_type'), search: true, source: {
                            0: {text: '轮播图'}, 1: {text: '导航'}, 2: {text: '广告'}, 3: {text: '分类展示'}, 4: {text: '通告'}
                        }, formatter: Formatter.status},
                        {field: 'type', title: __('Type'), source: {
                            '0': {text: '无跳转'}, '1': {text: '跳转到商品'}, '2': {text: '跳转到分类'}, '3': {text: '跳转到页面'}, '4': {text: '跳转到外链'}, '5': {text: '跳转到文章'}, '6': {text: '跳转到底部导航'}
                        }, formatter: Formatter.status, searchable: false},
                        {field: 'target', title: __('Target'), formatter: function(val, row, index) {
                            var result = "-";
                            switch(parseInt(row.type)) {
                                case 1 : {
                                    result = '<a target="_blank" href="' + base_url + 'pages/category/category?id=' + val +'"><i class="fa fa-link"></i></a>';
                                    break;
                                }
                                case 2 : {
                                    result = '<a target="_blank" href="' + base_url + 'pages/product/product?id=' + val +'"><i class="fa fa-link"></i></a>';
                                    break;
                                }
                                case 5: {
                                    var url = encodeURIComponent(window.location.origin + '/addons/xshop/article?id=' + val);
                                    result = '<a target="_blank" href="' + base_url + 'pages/webview/webview?url=' + url +'"><i class="fa fa-link"></i></a>';
                                    break;
                                }
                                default : {
                                    result = row.target;
                                    break;
                                }
                            }
                            return result;
                        }, searchable: false},
                        {field: 'params', title: __('Params'), searchable: false},
                        {field: 'sort', title: __('Sort'), searchable: false},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.toggle, searchable: false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
            $('#target-render-content').html(Template('normal'))
            Formatter.bindEvent($('#target-render-content'))
            $(document).on('change', '#c-type', function() {
                var target = $('option:selected', this).data('target')
                $('#target-render-content').html(Template(target))
                Formatter.bindEvent($('#target-render-content'))
            })
            Hook.listen('form')
        },
        edit: function () {
            Controller.api.bindevent();
            $('#target-render-content').html(Template($('option:selected', '#c-type').data('target')))
            Formatter.bindEvent($('#target-render-content'))
            $(document).on('change', '#c-type', function() {
                var target = $('option:selected', this).data('target')
                $('#target-render-content').html(Template(target))
                Formatter.bindEvent($('#target-render-content'))
            })
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