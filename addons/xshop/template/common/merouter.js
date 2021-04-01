import config from './request/config';
let meRouter = function(option) {
	let type = option.type || 0
	type = parseInt(type)
	switch (type) {
		case 1: {
			uni.navigateTo({
				url: '/pages/product/product?id=' + option.target
			})
			break;
		}
		case 2: {
			uni.navigateTo({
				url: '/pages/product/list?cat_id=' + option.target
			})
			break;
		}
		case 3: {
			uni.navigateTo({
				url: option.target
			})
			break;
		}
		case 4: {
			uni.navigateTo({
				url: '/pages/webview/webview?url=' + encodeURIComponent(option.target)
			})
			break;
		}
		case 5: {
			let url = config.baseUrl + 'xshop/article/index?id=' + option.target;
			uni.navigateTo({
				url: '/pages/webview/webview?url=' + encodeURIComponent(url)
			})
			break;
		}
		case 6: {
			uni.switchTab({
				url: option.target
			})
		}
		default : {
			break;
		}
	}
}

export default meRouter