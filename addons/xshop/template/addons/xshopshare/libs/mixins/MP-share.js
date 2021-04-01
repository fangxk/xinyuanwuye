import { mapState } from 'vuex';
export default {
	computed: {
		...mapState({
			app_info: state => state.app_info,
			user: state => state.user.userinfo
		})
	},
	onShareAppMessage() {
		let page = getCurrentPages()[getCurrentPages().length - 1];
		let options = page.options;
		if (this.user && !this.$tools.isEmpty(this.user.id)) {
			options.sid = this.user.id
		}
		let path = '/' + page.route;
		if (!this.$tools.isEmpty(options)) path += '?' + this.$tools.queryStringify(options);
		let title = this.app_info.config.share_title;
		let imageUrl = this.app_info.config.share_img;
		return {
			title,
			path,
			imageUrl
		}
	}
}