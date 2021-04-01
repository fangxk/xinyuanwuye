define([
    'jquery', 'upload', 'form'
], function($, Upload, Form) {
    'use strict';
    var image = {
        html: '<div class="i-modal"><div class="i-content"><p>上传/选择图片</p><button data-upload-success="onSuccess" type="button" id="plupload-i-image" class="plupload" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp"><i class="fa fa-upload"></i> 上传</button><button type="button" id="fachoose-i-image" class="fachoose" data-mimetype="image/*"><i class="fa fa-list"></i>选择</button><span class="close fa fa-close"></span></div></div>',
        css: "<style>.i-modal {background: rgba(0, 0, 0, 0.3); z-index: 9999; position: fixed;left: 0; top: 0; height: 100%; width: 100%;}.i-content{text-align: center;background: #fff; border-radius: 8px; width: 400px; height: 190px;position: absolute;left: 50%;top: 50%;-webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%);}.i-content button{width: 100px;height: 100px;color: #606266;border: 1px dashed #999;border-radius: 10px;background: none;margin: 0 5px;}.i-content button:hover{border-color: #409eff}.i-modal .i-content p {margin: 15px 0;}.i-modal .i-content .close{position: absolute;right: 5px; top: 5px; font-size: 20px;}</style>",
        el: '.i-modal',
        cb: function() {},
        open: function(cb) {
            var _this = this
            this.cb = cb
            Upload.api.custom['onSuccess'] = _this.cb
            $(this.el).show()
        },
        close: function() {
            $(this.el).hide()
        },
        init: function() {
            $(document.body).append(this.html)
            $(document.body).append(this.css)
            this.bindEvent()
            this.close()
        },
        bindEvent: function() {
            var _this = this
            Upload.api.plupload('.plupload', $(_this.el))
            $(".fachoose", _this.el).on('click', function () {
                var multiple = $(this).data("multiple") ? $(this).data("multiple") : false;
                var mimetype = $(this).data("mimetype") ? $(this).data("mimetype") : '';
                var admin_id = $(this).data("admin-id") ? $(this).data("admin-id") : '';
                var user_id = $(this).data("user-id") ? $(this).data("user-id") : '';
                parent.Fast.api.open("general/attachment/select?element_id=" + $(this).attr("id") + "&multiple=" + multiple + "&mimetype=" + mimetype + "&admin_id=" + admin_id + "&user_id=" + user_id, __('Choose'), {
                    callback: function(data) {
                        _this.cb(data)
                    }
                })
            })
            $('.close', _this.el).on('click', function() {
                _this.close()
            })
            $('.i-modal', document).on('click', function(e) {
                _this.close()
            })
            $('.i-content', '.i-modal').on('click', function(e) {
                e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true
            })
        }
    };
    return image;
});