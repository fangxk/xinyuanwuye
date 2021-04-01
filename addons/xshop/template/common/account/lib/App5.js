import Base from './Base.js'
import wxApi from '../../wxApi.js'

function App5() {
	this.appCode = 'app_appId'
	this.appCodeKey = 'xshop_app_appid'
	Base.call(this)
}
// #ifdef APP-PLUS

App5.prototype = Base.prototype

App5.prototype.constructor = App5

App5.prototype.login = async function(data = {}, success, fail) {
	return new Promise(async (resolve, reject) => {
		plus.oauth.getServices(async services => {
			let weixinService = null
			if (services && services.length) {
				for (var i = 0, len = services.length; i < len; i++) {
					if (services[i].id === 'weixin') {
						weixinService = services[i];
						break;
					}
				}
				if (!weixinService) {
					fail && fail('没有微信登录授权服务');
					return;
				}
				// http://www.html5plus.org/doc/zh_cn/oauth.html#plus.oauth.AuthService.authorize
				
				weixinService.authorize(event => {
					// success && success(event.code)
					this.http.post('vendor.login', {code: event.code}, {vendor: 'App'}).then(result => {
						success && success(result)
					}).catch(e => {
						uni.showModal({
							title: '提示',
							content:  JSON.stringify(e),
							showCancel: false,
							confirmText: '确定'
						});
						fail && fail(e)
					})
				}, function(error) {
					fail && fail('authorize fail:' + JSON.stringify(error));
				}, {
					// http://www.html5plus.org/doc/zh_cn/oauth.html#plus.oauth.AuthOptions
				});
			} else {
				fail && fail("无可用的登录授权服务")
			}
		}, e => {
			uni.showToast({
				title: JSON.stringify(e)
			})
			fail && fail(e)
		})
	})
}



App5.prototype.login2 = async function(info = {}, provider = 'weixin') {
	return new Promise((resolve, reject) => {
		try {
			let code = this.getCode()
			info.code = res.code
			this.http.post('vendor.login2', info, {vendor: 'App'}).then(result => {
				resolve(result)
			}).catch(e => {
				reject(e)
			})
		} catch(e) {
			reject(e)
		}
	})
}

App5.prototype.getAppid = async function() {
	return Promise((resolve, reject) => {
		let appid = uni.getStorageSync('appId')
		if (!appid) {
			this.http.post('index.config', {code: ['wx_app_appid']}).then(res => {
				appid = res.data['xshop_wx_app_appid']
				if (!!appid) {
					uni.setStorageSync('appId', appid)
					resolve(appid)
				} else {
					reject('appid错误')
				}
			}).catch(e => {
				reject(e)
			}) 
		} else {
			resolve(appid)
		}
	})
}
/**
App5.prototype.login = async function(data = {}, success, fail) {
	this.getCode({}, res => {
		data.code = code
		this.http.post('vendor.login', data, {vendor: 'App'}).then(result => {
			success && success(result)
		}).catch(e => {
			uni.showModal({
				title: '提示',
				content:  JSON.stringify(e),
				showCancel: false,
				confirmText: '确定'
			});
			fail && fail(e)
		})
	}, e => {
		uni.showModal({
			title: '提示',
			content:  e,
			showCancel: false,
			confirmText: '确定'
		});
		fail && fail(e)
	})
	
}
*/
// #endif
export default App5
