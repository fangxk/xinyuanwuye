define(['jquery', 'bootstrap', 'backend', 'table', 'form','jstree'], function ($, undefined, Backend, Table, Form,undefined) {
    Fast.config.openArea = ['90%','90%'];
    $.jstree.core.prototype.get_all_checked = function (full) {
        var obj = this.get_selected(), i, j;
        for (i = 0, j = obj.length; i < j; i++) {
            obj = obj.concat(this.get_node(obj[i]).parents);
        }
        obj = $.grep(obj, function (v, i, a) {
            return v != '#';
        });
        obj = obj.filter(function (itm, i, a) {
            return i == a.indexOf(itm);
        });
        return full ? $.map(obj, $.proxy(function (i) {
            return this.get_node(i);
        }, this)) : obj;
    };
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                showColumns: false,
                showExport: false,
                commonSearch: false,
                extend: {
                    index_url: 'property/activity/news/index' + location.search,
                    add_url: 'property/activity/news/add',
                    edit_url: 'property/activity/news/edit',
                    del_url: 'property/activity/news/del',
                    multi_url: 'property/activity/news/multi',
                    dragsort_url: 'ajax/weigh',
                    table: 'activity',
                }

            });
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "文章标题";};

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
                        {field: 'desc', title: __('Desc')},
                        {field: 'picimage', title: __('Picimage'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        /*{field: 'votetype', title: __('Votetype'), searchList: {"1":__('Votetype 1'),"0":__('Votetype 0')}, formatter: Table.api.formatter.normal},*/
                        /*{field: 'num', title: __('Num')},*/
                        {field: 'createtime', title: "创建时间", operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        /*{field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},*/
                        /*{field: 'signtime', title: __('Signtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},*/
                        {field: 'statusswitch', title: __('Statusswitch'), searchList: {"0":__('Statusswitch 0'),"1":__('Statusswitch 1')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'istop', title:"是否置顶"},
                        /*{field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'provice', title: __('Provice')},
                        {field: 'city', title: __('City')},
                        {field: 'dist', title: __('Dist')},*/
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"0":__('Status 0')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            $("input[name='row[status]']").click(function(){
                var type = $("input[name='row[status]']:checked").val();
                if(type == 1){
                    $("#p1").hide();
                }
                if(type == 0){
                    $("#p1").show();
                }
            });
        },
        edit: function () {
            Controller.api.bindevent();
            $("input[name='row[status]']").click(function(){
                var type = $("input[name='row[status]']:checked").val();
                if(type == 1){
                    $("#p1").hide();
                }
                if(type == 0){
                    $("#p1").show();
                }
            });
        },
        api: {
            /*bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }*/
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"), null, null, function () {
                    if ($("#treeview").size() > 0) {
                        var r = $("#treeview").jstree("get_all_checked");
                        $("input[name='row[reginonid]']").val(r.join(','));
                    }
                    return true;
                });
                //渲染权限节点树
                //销毁已有的节点树
                $("#treeview").jstree("destroy");
                Controller.api.rendertree(nodeData);
                //全选和展开
                $(document).on("click", "#checkall", function () {
                    $("#treeview").jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                });
                $(document).on("click", "#expandall", function () {
                    $("#treeview").jstree($(this).prop("checked") ? "open_all" : "close_all");
                });
                $("select[name='row[pid]']").trigger("change");
            },
            rendertree: function (content) {
                $("#treeview")
                    .on('redraw.jstree', function (e) {
                        $(".layer-footer").attr("domrefresh", Math.random());
                    })
                    .jstree({
                        "themes": {"stripes": true},
                        "checkbox": {
                            "keep_selected_style": false,
                        },
                        "types": {
                            "root": {
                                "icon": "fa fa-folder-open",
                            },
                            "menu": {
                                "icon": "fa fa-folder-open",
                            },
                            "file": {
                                "icon": "fa fa-file-o",
                            }
                        },
                        "plugins": ["checkbox", "types"],
                        "core": {
                            'check_callback': true,
                            "data": content
                        }
                    });
            }
        }
    };
    return Controller;
});