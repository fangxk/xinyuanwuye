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
                    index_url: 'home/banner/index' + location.search,
                    add_url: 'home/banner/add',
                    edit_url: 'home/banner/edit',
                    del_url: 'home/banner/del',
                    multi_url: 'home/banner/multi',
                    table: 'banner',
                }
            });
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "轮播标题";};
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),visible:false},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'title', title: __('Title')},
                        {field: 'link', title: __('Link'),visible:false},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange',formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange',formatter: Table.api.formatter.datetime},
                        {field: 'showswitch', title: __('Showswitch'), searchList: {"1":__('Showswitch 1'),"0":__('Showswitch 0')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            $(".typess").click(function () {
                var radios = $(this).val();
                if(radios==1){//小程序
                    $("#links").hide();
                    $("#titles").show();
                }else{
                    $("#titles").hide();
                    $("#links").show();
                }
            });
        },
        edit: function () {
            Controller.api.bindevent();
            $(".typess").click(function () {
                var radios = $(this).val();
                if(radios==1){//小程序
                    $("#links").hide();
                    $("#titles").show();
                }else{
                    $("#titles").hide();
                    $("#links").show();
                }
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});