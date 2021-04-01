import Base from './Base.js'
import wxApi from '../../wxApi.js'
function H5() {
	this.appCode = 'h5_appId'
	this.appCodeKey = 'xshop_h5_appid'
	Base.call(this)
}
H5.prototype = Base.prototype

H5.prototype.constructor = H5

H5.prototype.getQuery = function() {
	let query = window.location.search
	if (!!query) {
		let arr = query.substring(1, query.length).split('&')
		let obj = {}
		arr.forEach((item, i) => {
			let _a = item.split('=')
			obj[_a[0]] = decodeURIComponent(_a[1])
		})
		return obj
	}
	return {}
}

H5.prototype.login = async function(option = {}) {
	let appId = await this.getAppId()
	let state = Math.floor(Math.random() * 100000)
	uni.setStorageSync('wx_auth_state', state)
	let url = option.url || window.location.url
	let redirect_uri = encodeURIComponent(wxApi.redirectUrl(url))
	window.location.href = `https://open.weixin.qq.com/connect/oauth2/authorize?appid=${appId}&redirect_uri=${redirect_uri}&response_type=code&scope=snsapi_userinfo&state=${state}#wechat_redirect`
}




export default H5