define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    Fast.config.openArea = ['90%','90%'];
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
        /*edit: function () {
            Controller.api.bindevent();
        },*/
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});