define(['jquery', 'bootstrap', 'backend', 'table', 'form','jstree'], function ($, undefined, Backend, Table, Form, undefined) {
    Fast.config.openArea = ['100%','80%'];
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
                    index_url: 'property/question/index' + location.search,
                    add_url: 'property/question/add',
                    edit_url: 'property/question/edit',
                    del_url: 'property/question/del',
                    multi_url: 'property/question/multi',
                    table: 'question',
                }
            });
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "问卷标题";};
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id',title: __('Id')},
                        {field: 'weigh', title: __('Weigh'),visible:false},
                        {field: 'title', title: __('Title')},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'image', title: __('Image'),visible:false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'infoimage', title: __('Infoimage'), visible:false,events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'desc', visible:false,title: __('Desc')},
                        {field: 'infodesc',visible:false, title: __('Infodesc')},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"0":__('Status 0')}, formatter: Table.api.formatter.status},
                        {field: 'switch', title: __('Switch'), searchList: {"1":__('Switch 1'),"0":__('Switch 0')}, table: table, formatter: Table.api.formatter.toggle},
                        {
                            field: 'operate',
                            width: "120px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            operate: false,
                            buttons: [
                                {//添加问题
                                    name: "detail",
                                    text: __('添加问题'),
                                    title: __('添加问题'),
                                    classname: 'btn btn-success',
                                    url: "property/question/addquestion",
                                    visible: function (row) {
                                        if(row.isadd == '0'){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {//修改问题
                                    name:'addtabs',
                                    text: __('修改问题'),
                                    title: __('修改问题'),
                                    classname: 'btn btn-success',
                                    url:"property/question/editquestion",
                                    visible: function (row) {
                                        if(row.isadd== '1'){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {//统计
                                    name: 'eidt',
                                    text: __('统计信息'),
                                    title: __('统计信息'),
                                    classname: 'btn btn-info btn-sm',
                                    url: 'property/question/suminfo',
                                },
                                {//详细
                                    name: 'eidt',
                                    text: __('详细信息'),
                                    title: __('详细信息'),
                                    classname: 'btn btn-info btn-sm',
                                    url: 'property/question/questionshow',
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
                                    confirm: '确认删除吗？',
                                    url: 'property/question/del',
                                    success: function (data, ret) {
                                        Layer.alert("删除成功");
                                        table.bootstrapTable('refresh');
                                    },
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                                {//初始化数据
                                    name: 'detail',
                                    text:function(row){if (row.isinit==1) {return "初始化数据";}return "暂无数据";},
                                    classname: 'btn btn-warning btn-sm btn-ajax',
                                    url: 'property/question/initdata',
                                    confirm: '初始化数据吗？',
                                    disable: function (row) {if (row.isinit==1){return false;}return true;},
                                    success: function (data, ret) {
                                        Layer.alert("初始化成功");
                                        table.bootstrapTable('refresh');
                                    },
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                }
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
        suminfo:function(){
            Controller.api.bindevent();
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
       /* api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }*/
    };
    return Controller;
});