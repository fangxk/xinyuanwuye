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
                    index_url: 'xshop/upgrade/index' + location.search,
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
                        {field: 'package_version', title: __('版本'), formatter: function(val, row, index) {
                            return  val + '<a  href="javascript:;" class="title" title="' + val  + ' 使用说明" data-row_index="' + index +'"><i style="color: red;margin-left: 10px;font-size: 15px;" class="fa fa-question-circle-o"></i></a>'
                        }},
                        {field: 'description', title: __('更新简介')},
                        {field: 'create_time_text', title: __('更新时间')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate, buttons: [
                            {name: 'add'}, {name: 'edit'},
                            {
                                name: 'update', text: __('在线升级'),
                                classname: 'btn btn-xs btn-primary btn-upgrade'
                            }
                        ]}
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
                    var row = table.bootstrapTable('getData')[$(this).data('row_index')];
                    var file_html = "<h4 style='text-align: center;color:#de1818;'>—— 本次更新会覆盖以下文件，请备份相关文件！——</h4><ul>";
                    $.each(row.files.split(','), function(index, item) {
                        file_html += "<li>" + item +"</li>";
                    })
                    file_html += "</ul>";
                    top.Layer.open({
                        title: '更新说明',
                        content: row.content + file_html,
                        area: ['50%', '70%']
                    })
                })

                $('.btn-upgrade').on('click', function() {
                    var row = table.bootstrapTable('getData')[$(this).data('row-index')];
                    var index = Layer.open({
                        title: '警告',
                        icon: 3,
                        btn: ['确定', '取消'],
                        content: '<h4 style="color:red;">在线升级会覆盖文件，请先查看更新说明，备份相关文件！</h4>',
                        closeBtn: true,
                        yes: function() {
                            var data = {
                                addon_name: row.addon_name,
                                package_version: row.package_version,
                                version: row.version
                            };
                            Fast.api.ajax({
                                url: 'xshop/upgrade/install',
                                data: data
                            }, function() {
                                Layer.close(index);
                                table.bootstrapTable('refresh');
                            })
                        }
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