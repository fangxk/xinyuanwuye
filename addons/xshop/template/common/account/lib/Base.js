import http from '../../http'
function Base() {
	this.http = http
	this.getAppId = async function() {
		let appId = uni.getStorageSync('appId')
		if (!appId) {
			let res = await http.post('index.config', {code: [this.appCode]})
			appId = res.data[this.appCodeKey]
			uni.setStorageSync('appId', appId)
		}
		return appId
	}
}
export default Base