import Base from './Base.js'
import wxApi from '../../wxApi.js'
function WechatMp() {
	this.appCode = 'wx_mp_appid'
	this.appCodeKey = 'xshop_wx_mp_appid'
	Base.call(this)
}
WechatMp.prototype = Base.prototype

WechatMp.prototype.constructor = WechatMp

WechatMp.prototype.isLogin = async function () {
	return new Promise((resolve, reject) => {
		wx.checkSession({
			success() {
				resolve(true)
			},fail() {
				resolve(false)
			}
		})
	})
}

WechatMp.prototype.getCode = async function () {
	return new Promise((resolve, reject) => {
		wx.login({
			success(res) {
				if (res.code) {
					resolve(res.code)
				} else {
					reject(res)
				}
			}
		})
	})
}

WechatMp.prototype.getScope = async function (code) {
	return new Promise((resolve, reject) => {
		wx.getSetting({
			success(res) {
				if (!res.authSetting[code]) {
					wx.authorize({
						scope: code,
						success() {
							resolve()
						},
						fail(res) {
							resolve()
						}
					})
				} else {
					resolve()
				}
			}
		})
	})
}

WechatMp.prototype.login2 = async function (info = {}) {
	let _this = this
	return new Promise((resolve, reject) => {
		wx.login({
			success(res) {
				if (res.code) {
					info.code = res.code
					_this.http.post('vendor.login2', info, {vendor: 'WechatMp'}).then(result => {
						resolve(result)
					}).catch(e => {
						reject(e)
					})
				}
			}
		})
	})
}

WechatMp.prototype.login = async function (data = {}) {
	data.openid = await this.getOpenId();
	return new Promise((resolve, reject) => {
		this.http.post('vendor.login', data, {vendor: 'WechatMp'}).then(result => {
			resolve(result)
		}).catch(e => {
			reject(e)
		})
	})
}

WechatMp.prototype.getOpenId = async function () {
	return new Promise(async (resolve, reject) => { 
		if (!uni.getStorageSync('openId') || !(await this.isLogin())) {
			let res = await this.login2()
			if (res.data.openid) resolve(res.data.openid);
			else reject(data)
		} else {
			resolve(uni.getStorageSync('openId'));
		}
	})
}

export default WechatMp