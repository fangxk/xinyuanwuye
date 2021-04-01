<script>
	import {
		mapMutations,
		mapActions,
		mapState
	} from 'vuex';
	// #ifdef MP-WEIXIN
	import Account from './common/account/Account'
	// #endif
	
	export default {
		methods: {
			...mapActions(['getUserinfo', 'getAppInfo']),
			...mapMutations(['SAVE_USERINFO']),
			appUpdate() {
				let _this = this
				plus.runtime.getProperty(plus.runtime.appid, function(widgetInfo) {
					let platform = uni.getSystemInfoSync().platform
					_this.$http.post('update.v2', {version: widgetInfo.version, name: widgetInfo.name, platform}).then(res => {
						var data = res.data
						if (data.update == 1 && data.type == 0 && data.silent == 1) {
							uni.downloadFile({
								url: data.wgtUrl,
								success: function(downloadResult) {
									if (downloadResult.statusCode == 200) {
										plus.runtime.install(downloadResult.tempFilePath, {
											force: false
										}, function() {
											uni.setStorageSync('xshop.is_updated', 1);
										})
									}
								}
							})
						} else if (data.update == 1 && data.type == 1) {
							uni.showModal({
								title: '检测到更新，是否下载？',
								content: data.description,
								confirmText: '确定',
								cancelText: "取消",
								success: e => {
									if (e.confirm) {
										plus.runtime.openURL(data.wgtUrl)
									}
								}
							})
						}
					})
				})
			},
			async login() {
				// #ifdef MP-WEIXIN
				let account = Account.init('WechatMp')
				if (!uni.getStorageSync('openId') || !(await account.isLogin())) {
					let res = await account.login2()
					if (res.data.openid) uni.setStorageSync('openId', res.data.openid)
					if (res.data.userinfo) {
						uni.setStorageSync('token', res.data.userinfo.token)
						this.SAVE_USERINFO(res.data.userinfo)
					}
				}
				// #endif
			},
			async getAppId() {
				if (!uni.getStorageSync('appId')) {
					let code = ''
					// #ifdef MP-WEIXIN
					code = 'wx_mp_appid'
					// #endif
					// #ifdef MP-TOUTIAO
					code = 'tt_mp_appid'
					// #endif
					// #ifdef H5
					if (this.$wxApi.isweixin()) {
						code = 'h5_appid'
					}
					// #endif
					if (!!code) {
						let res = await this.fetchAppId(code)
						return Promise.resolve(res)
					}
					return Promise.reject()
				}
			},
			async fetchAppId(code) {
				let res = await this.$http.post('index.config', {
					code: [code]
				})
				uni.setStorageSync('appId', res.data['xshop_' + code])
				return Promise.resolve(res)
			},
			launchLog() {
				let form = {
					systeminfo: uni.getSystemInfoSync()
				}
				this.$http.post('index.launch', form)
			},
		},
		onLaunch: async function() {
			uni.setStorageSync('xshop.is_updated', 0);
			// #ifdef MP-WEIXIN
			this.login()
			// #endif
			await this.getAppInfo()
			await this.getUserinfo().catch(e => {})
			this.launchLog()
			this.getAppId().then(res => {}).catch(e => {})
			// #ifdef APP-PLUS
			this.appUpdate()
			// #endif
		},

		onShow: function(options) {
		},
		onHide: function() {}
	}
</script>
<style lang='scss'>
	@import 'static/css/iconfont.css';
	@import 'static/css/index.css'; 

	view,
	scroll-view,
	swiper,
	swiper-item,
	cover-view,
	cover-image,
	icon,
	text,
	rich-text,
	progress,
	button,
	checkbox,
	form,
	input,
	label,
	radio,
	slider,
	switch,
	textarea,
	navigator,
	audio,
	camera,
	image,
	video {
		box-sizing: border-box;
	}
	.video {
		width: 100%;
	}

	/* 骨架屏替代方案 */
	.Skeleton {
		background: #f3f3f3;
		padding: 20upx 0;
		border-radius: 8upx;
	}

	/* 图片载入替代方案 */
	.image-wrapper {
		font-size: 0;
		background: #f3f3f3;
		border-radius: 4px;

		image {
			width: 100%;
			height: 100%;
			transition: .6s;
			opacity: 0;

			&.loaded {
				opacity: 1;
			}
		}
	}

	.clamp {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		display: block;
	}

	.common-hover {
		background: #f5f5f5;
	}

	/*边框*/
	.b-b:after,
	.b-t:after {
		position: absolute;
		z-index: 3;
		left: 0;
		right: 0;
		height: 0;
		content: '';
		transform: scaleY(.5);
		border-bottom: 1px solid $border-color-base;
	}

	.b-b:after {
		bottom: 0;
	}

	.b-t:after {
		top: 0;
	}

	/* button样式改写 */
	uni-button,
	button {
		height: 80upx;
		line-height: 80upx;
		font-size: $font-lg + 2upx;
		font-weight: normal;

		&.no-border {
			border: none;
		}

		&.no-border:before,
		&.no-border:after {
			border: 0;
		}
	}

	uni-button[type=default],
	button[type=default] {
		color: $font-color-dark;
	}

	/* input 样式 */
	.input-placeholder {
		color: #999999;
	}

	.placeholder {
		color: #999999;
	}
</style>
