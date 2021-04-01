define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'property/notice/index' + location.search,
                    add_url: 'property/notice/add',
                    edit_url: 'property/notice/edit',
                    del_url: 'property/notice/del',
                    multi_url: 'property/notice/multi',
                    table: 'notice',
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
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'id', title: __('Id'),visible:false},
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});