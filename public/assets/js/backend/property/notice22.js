define(['jquery', 'bootstrap', 'backend', 'table', 'form','jstree'], function ($, undefined, Backend, Table, Form,undefined) {
    Fast.config.openArea = ['80%','80%'];
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
                    index_url: 'property/notice/index' + location.search,
                    add_url: 'property/notice/add',
                    edit_url: 'property/notice/edit',
                    del_url: 'property/notice/del',
                    multi_url: 'property/notice/multi',
                    table: 'notice',
                }
            });
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "公告标题";};
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'Weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'id', title: __('Id'),visible:false},
                        {field: 'labeldata', title: '公告标签'},
                        {field: 'title', title: __('Title')},
                        {field: 'desc', title: __('Desc'),visible:false},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange',visible:false},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange',visible:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'link', title: __('Link'),visible:false},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'provice', title: __('Provice'),visible:false},
                        {field: 'city', title: __('City'),visible:false},
                        {field: 'district', title: __('District'),visible:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
            //城市显示信息
            $('#obtain').click(function(){
                var city =$('.select-item').eq('1').text();
                if(city){
                    Fast.api.ajax({
                        url:'ajax/obtain',
                        type:'post',
                        data:{name:city}
                    }, function(res){
                        var region ="";
                        region += "<div class='checkbox checkbox-success checkbox-inline' >";
                        region += "<input type='checkbox' id='checkAll' name='checkAll' data-group='regionid'  />";
                        region += "<label for='checkAll'>全部</label></div>";
                        if(res.code==1){
                            var data = res.data;

                            for (var o=0;o<data.length;o++) {
                                var che = '';
                                if(regionids){
                                    if($.inArray(data[o].pk,regionids)!=-1){
                                        che = 'checked';
                                    }
                                }
                                region += "<div class='checkbox checkbox-success checkbox-inline'>";
                                region += "<input type='checkbox' id='regionid_"+data[o].pk+"' value='" + data[o].pk + "' data-group='regionid' name=reginonid[]"+che+">" ;
                                region += "<label for='regionid_"+data[o].pk+"'>"+data[o].name+" </label></div>";
                            }
                            $('#con').html(region);
                            $("#checkAll").click(function() {
                                var checked = $(this).get(0).checked;
                                var group = $(this).data('group');
                                $("input:checkbox[data-group='" +group + "']").each(function(){
                                    $(this).get(0).checked = checked;
                                })
                            });
                        }else{
                            Layer.msg(res.msg);
                        }
                    }, function(res){
                        //失败的回调
                        return false;
                    });
                }else{
                    Layer.msg('请选择省市区！');
                }
            });
            /*城市显示信息结束*/
        },
        edit: function () {
            Controller.api.bindevent();

            //展示城市
           if(citys){
                   Fast.api.ajax({
                        url:'ajax/obtain',
                        type:'post',
                        data:{name:citys}
                    }, function(res){

                        var region ="";
                        region += "<div class='checkbox checkbox-success checkbox-inline' >";
                        region += "<input type='checkbox' id='checkAll' name='checkAll' data-group='regionid'  />";
                        region += "<label for='checkAll'>全部</label></div>";
                        if(res.code==1){
                            var data = res.data;

                            for (var o=0;o<data.length;o++) {
                                var che = '';
                                if(regionids){
                                    if($.inArray(data[o].pk,regionids)!=-1){
                                        che = 'checked';
                                    }
                                }
                                region += "<div class='checkbox checkbox-success checkbox-inline'>";
                                region += "<input type='checkbox' id='regionid_"+data[o].pk+"' value='" + data[o].pk + "' data-group='regionid' name='reginonid[]'"+che+">" ;
                                region += "<label for='regionid_"+data[o].pk+"'>"+data[o].name+" </label></div>";
                            }

                            $('#con').html(region);
                            $("#checkAll").click(function() {
                                var checked = $(this).get(0).checked;
                                var group = $(this).data('group');
                                $("input:checkbox[data-group='" +group + "']").each(function(){
                                    $(this).get(0).checked = checked;
                                });
                            });
                        }else{
                            Layer.msg(res.msg);
                        }
                    }, function(res){
                        //失败的回调
                        return false;
                    });
            };

            $("input[name='row[status]']").click(function(){
                var type = $("input[name='row[status]']:checked").val();
                if(type == 1){
                    $("#p1").hide();
                }
                if(type == 0){
                    $("#p1").show();
                }
            });

            //城市显示信息
            $('#obtain').click(function(){
                var city =$('.select-item').eq('1').text();
                if(city){
                    Fast.api.ajax({
                        url:'ajax/obtain',
                        type:'post',
                        data:{name:city}
                    }, function(res){

                        var region ="";
                        region += "<div class='checkbox checkbox-success checkbox-inline' >";
                        region += "<input type='checkbox' id='checkAll' name='checkAll' data-group='regionid'  />";
                        region += "<label for='checkAll'>全部</label></div>";
                        if(res.code==1){
                            var data = res.data;

                            for (var o=0;o<data.length;o++) {
                                var che = '';
                                if(regionids){
                                    if($.inArray(data[o].pk,regionids)!=-1){
                                        che = 'checked';
                                    }
                                }
                                region += "<div class='checkbox checkbox-success checkbox-inline'>";
                                region += "<input type='checkbox' id='regionid_"+data[o].pk+"' value='" + data[o].pk + "' data-group='regionid' name=reginonid[]"+che+">" ;
                                region += "<label for='regionid_"+data[o].pk+"'>"+data[o].name+" </label></div>";
                            }
                            $('#con').html(region);
                            $("#checkAll").click(function() {
                                var checked = $(this).get(0).checked;
                                var group = $(this).data('group');
                                $("input:checkbox[data-group='" +group + "']").each(function(){
                                    $(this).get(0).checked = checked;
                                })
                            });
                        }else{
                            Layer.msg(res.msg);
                        }
                    }, function(res){
                        //失败的回调
                        return false;
                    });
                }else{
                    Layer.msg('请选择省市区！');
                }
            });
            /*城市显示信息结束*/
        },
        /*bindevent: function () {
            Form.api.bindevent($("form[role=form]"));
        }*/
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
                console.log(content);
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