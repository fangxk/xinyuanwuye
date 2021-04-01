import xshop from './xshop'
import xshopcoupon from './xshopcoupon'
import xshopgroupon from './xshopgroupon'
import xshopmsg from './xshopmsg';
const modules = {
	xshop,
	xshopcoupon,
	xshopgroupon,
	xshopmsg
}
function parseUrl() {
	let res = {}
	for (let key in modules) {
		let item = modules[key]
		for (let k in item) {
			res[k] = item[k]
			res[k].uri = key + '/' + item[k].uri
		}
	}
	return res
}
export default parseUrl()