import WechatMp from './lib/WechatMp.js'
import App5 from './lib/App5.js'
export default {
	services: {
		WechatMp: new WechatMp(),
		App5: new App5()
	},
	init: function(key) {
		return this.services[key]
	}
}