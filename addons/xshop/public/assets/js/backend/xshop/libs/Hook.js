define([], function() {
    'use strict';
    var hooks = window.hooks
    var hook = {
        handlers: {},
        setHanders: function() {
            if (hooks) {
                var _this = this
                var keys = Object.keys(hooks)
                for (var i = 0; i < keys.length; i ++) {
                    var items = hooks[keys[i]]
                    var action = require(keys[i])
                    for (var j = 0; j < items.length; j ++) {
                        var item = items[j]
                        if (_this.handlers[item.name]) {
                            _this.handlers[item.name].push(action[item.method])
                        } else {
                            _this.handlers[item.name] = [action[item.method]]
                        }
                    }
                }
                this.cb && this.cb(this.handlers)
            }
        },
        init: function(cb) {
            this.cb = cb
            var _this = this
            if (hooks) {
                var keys = Object.keys(hooks)
                var loaded = 0
                for (var i = 0; i < keys.length; i ++) {
                    require([keys[i]], function() {
                        loaded += 1
                        if (loaded == keys.length) _this.setHanders()
                    })
                }
            } else cb && cb(_this.handlers)
        },
        listen: function(name, payload) {
            if (!this.handlers[name]) {
                return payload
            }
            for (var i = 0; i < this.handlers[name].length; i ++) {
                payload = this.handlers[name][i](payload)
            }
            return payload
        }
    }
    return hook;
});