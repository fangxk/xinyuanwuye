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
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate}
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});