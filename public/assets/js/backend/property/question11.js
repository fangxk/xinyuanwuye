define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    Fast.config.openArea = ['100%','80%'];
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