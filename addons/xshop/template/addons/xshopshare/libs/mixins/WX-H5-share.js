import { mapState, mapActions } from 'vuex';
export default {
	computed: {
		...mapState({
			share_config_params: state => state.share.config,
			app_info: state => state.app_info,
			user: state => state.user.userinfo
		})
	},
	methods: {
		...mapActions(['getShareSignParams']),
		getShareParam() {
			let title = null, img = null, desc = null, link = null;
			return {title, img, desc, link}
		},
		async wxH5Share() {
			if (this.$tools.getPlatform() == 'WX-H5') {
				if (!this.share_config_params) await this.getShareSignParams();
				let { title, img, desc, link } = this.getShareParam();
				link = link || window.location.href;
				title = title || this.app_info.config.share_title;
				img = img || this.app_info.config.share_img;
				desc = desc || this.app_info.config.share_desc;
				if (this.user && !this.$tools.isEmpty(this.user.id)) {
					let id = this.user.id;
					let tag = '__TAG__';
					link = link.replace(/(\?sid=)([0-9]+)/, '?' + tag);
					link = link.replace(/(\&sid=)([0-9]+)/, '&' + tag);
					if (this.app_info.config.__H5_ROUTE_MODE__ == 'hash'&& link.indexOf(tag) == -1) {
						if (link.split('#').length > 1 && link.split('#')[1].indexOf('?') > -1) {
							link += '&' + tag;
						} else {
							link += '?' + tag;
						}
					} else if (link.indexOf(tag) == -1) {
						if (link.indexOf('?') > -1) link += tag;
						else link += '?' + tag;
					}
					link = link.replace(tag, 'sid=' + id);
				}
				this.$wxApi.wxRegister(this.share_config_params, title, img, desc, link)
			}
		}
	}
}