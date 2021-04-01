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
                    index_url: 'property/activity/activity/index' + location.search,
                    add_url: 'property/activity/activity/add',
                    edit_url: 'property/activity/activity/edit',
                    del_url: 'property/activity/activity/del',
                    multi_url: 'property/activity/activity/multi',
                    table: 'activity',
                }
            });
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "活动标题";};
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
                        {field: 'weigh', title: __('Weigh'),visible:false},
                        {field: 'title', title: __('Title')},
                        /*{field: 'desc', title: __('Desc'),visible:false},
                        {field: 'picimage',visible:false,title: __('Picimage'), events: Table.api.events.image, formatter: Table.api.formatter.image},*/
                        /*{field: 'number', title: __('Number')},*/
                        {field: 'num', title: __('Num')},
                        {field: 'starttime',title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'endtime',title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'signtime',title: __('Signtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'statusswitch',title: __('Statusswitch'), searchList: {"0":__('Statusswitch 0'),"1":__('Statusswitch 1')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'istop', title:"是否置顶"},
                        /*{field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},*/
                        /*{field: 'province', title: __('Province'),visible:false},
                        {field: 'city', title: __('City'),visible:false},
                        {field: 'dist', title: __('Dist'),visible:false},*/
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"0":__('Status 0')}, formatter: Table.api.formatter.status},
                        /*{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},*/
                        {
                            field: 'operate',
                            width: '120px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            operate: false,
                            buttons: [
                                {//报名管理
                                    name: "detail",
                                    text: __('报名管理'),
                                    title: __('报名管理'),
                                    classname: 'btn btn-success',
                                    url: "property/Activity/activity/areport",
                                },
                                {//修改问卷
                                    name: 'eidt',
                                    text: __('编辑'),
                                    title: __('编辑问卷'),
                                    icon:"fa fa-pencil",
                                    classname: 'btn btn-warning btn-sm btn-editone',
                                },
                                {//删除问卷
                                    name: 'detail',
                                    text:"删除",
                                    icon:"fa fa-trash",
                                    classname: 'btn btn-default btn-sm btn-ajax',
                                    url: 'property/Activity/activity/del',
                                    confirm: '确认删除吗？',
                                    success: function (data, ret) {
                                        Layer.alert("删除成功");
                                        table.bootstrapTable('refresh');
                                    },
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons
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
        /*edit: function () {
            Controller.api.bindevent();
        },*/
        api: {
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
        /*api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }*/
    };
    return Controller;
});