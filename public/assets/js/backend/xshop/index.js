requirejs.config({
    paths: {
        vue: 'backend/xshop/libs/vue',
        ELEMENT: 'backend/xshop/libs/element-ui',
    },
    shim: {
        ELEMENT: ['vue']
    }
})
define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'jstree', 'vue', 'ELEMENT', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Table, Form, jstree, Vue, ELEMENT, Echarts, undefined) {

    var Controller = {
        index: function() {
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');
            var option = {
                title: {
                    text: '销售订单笔数'
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                toolbox: {
                    feature: {
                        saveAsImage: {}
                    }
                },
                tooltip: {
                    trigger: 'axis'
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Orderdata.column
                },
                yAxis: {},
                series: Orderdata.series
            }
            myChart.setOption(option);
            $(window).resize(function () {
                myChart.resize();
            });
            Form.api.bindevent($("form[role=form]"), function(data, ret) {
                var info = data.totalInfo
                $('.paied').text(info.order_paied.count + ' (￥' + (info.order_paied.order_price || 0) + ')')
                $('.wait_pay').text(info.order_wait_pay.count + ' (￥' + (info.order_wait_pay.order_price || 0) + ')')
                $('.shipped').text(info.order_shipped.count + ' (￥' + (info.order_shipped.order_price || 0) + ')')
                $('.wait_ship').text(info.order_wait_ship.count + ' (￥' + (info.order_wait_ship.order_price || 0) + ')')
                option.title.text = $('#type').find('option:selected').text()
                option.xAxis.data = data.orderData.column
                option.series = data.orderData.series
                myChart.setOption(option)
                var html = ""
                for (var i = 0; i < data.productsMoney.length; i ++) {
                    var item = data.productsMoney[i]
                    html += "<li><a><span>" + item.title + "</span><span>￥" + item.product_price + '</span>'
                }
                $('.products-money ol').html(html)

                html = ""
                for (var i = 0; i < data.productsNumber.length; i ++) {
                    var item = data.productsNumber[i]
                    html += "<li><a><span>" + item.title + "</span><span>" + item.quantity + '</span>'
                }
                $('.products-number ol').html(html)
            });
            Form.api.bindevent($('.panel-info'))
        },
        cityselector: function() {
            Vue.use(ELEMENT)
            var vm = new Vue({
                el: '#app',
                data() {
                    return {
                        data: [],
                        selections: [],
                        checkedKeys: [],
                        loading: true
                    }
                },
                created() {
                    var ids = Fast.api.query('ids')
                    if (ids) this.checkedKeys = ids.split(',')
                    this.loadData()
                },
                methods: {
                    loadData() {
                        var l = this.$loading()
                        this.loading = false
                        $.ajax({
                            url: 'xshop/index/citySelector',
                            success: function(res) {
                                l.close()
                                vm.loading = false
                                vm.data = res.data
                            }
                        })
                    },
                    change(data, rows) {
                        this.selections = rows
                    },
                    submit() {
                        Fast.api.close(this.selections)
                    }
                }
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    }
    return Controller
})