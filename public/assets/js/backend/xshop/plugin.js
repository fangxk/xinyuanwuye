define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'backend/xshop/libs/Hook'], function ($, undefined, Backend, Table, Form, Template, Hook) {

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
                    index_url: 'xshop/plugin/index' + location.search,
                }
            });

            var table = $("#table");

            var config = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                pagination: false,
                search: false,
                commonSearch: false,
                columns: [
                    [
                        {field: 'title', title: __('插件名称'), formatter: function(val, row, index) {
                            return  val + '<a  href="javascript:;" class="title" title="' + val  + ' 使用说明" data-row_index="' + index +'"><i style="color: red;margin-left: 10px;font-size: 15px;" class="fa fa-question-circle-o"></i></a>'
                        }},
                        {field: 'remark', title: '插件描述'},
                        {field: 'version', title: '版本', formatter: function(val, row, index) {
                            return row.items[0].version
                        }},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function(val, row, index) {
                            return Template('operateTpl', {row: row, index: index})
                        }}
                    ]
                ]
            }
            config = Hook.listen('table_config', config)
            // 初始化表格
            table.bootstrapTable(config);

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('post-body.bs.table', function() {
                $('.title', this).on('click', function(e) {
                    var row = table.bootstrapTable('getData')[$(this).data('row_index')]
                    var content = "";
                    $.each(row.items, function(index, item) {
                        content += '<b>更新日志 ' + item.version + '</b>：<br/>' + item.content +"<br/>";
                    })
                    parent.Layer.open({
                        title: row.title + ' 使用说明',
                        content: content,
                        area: ['50%', '70%']
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});