<template>
	<view class="app">
		<view class="price-box">
			<text>支付金额</text>
			<text class="price">{{orderInfo.order_price}}</text>
		</view>
		<view class="count-down-box" v-if="orderInfo.left_time > 0">
			<text>请在 </text>
			<view class="time">
				<count-down @callback="reGetData" :targetTime="parseInt(orderInfo.left_time)"></count-down>
			</view>
			<text> 内完成支付</text>
		</view>

		<view class="pay-type-list">
			<view class="type-item b-b" @click="changePayType('wechat')" v-if="payTypeArr.indexOf('wechat') > -1">
				<text class="icon iconfont icon19weixinzhifu"></text>
				<view class="con">
					<text class="tit">微信支付</text>
				</view>
				<label class="radio">
					<radio value="" color="#fa436a" :checked='payType == "wechat" || payTypeArr.length == 1' />
					</radio>
				</label>
			</view>
			<view class="type-item b-b" @click="changePayType('alipay')" v-if="payTypeArr.indexOf('alipay') > -1">
				<text class="icon iconfont iconalipay"></text>
				<view class="con">
					<text class="tit">支付宝支付</text>
				</view>
				<label class="radio">
					<radio value="" color="#fa436a" :checked='payType == "alipay" || payTypeArr.length == 1' />
					</radio>
				</label>
			</view>
		</view>

		<text class="mix-btn" @click="confirm">确认支付</text>
	</view>
</template>

<script>
	import CountDown from '@/components/min-countdown'
	export default {
		components: {
			CountDown
		},
		data() {
			return {
				payType: "wechat",
				orderInfo: {},
				// #ifdef H5
				openId: null,
				// #endif
			};
		},
		computed: {
			payTypeArr() {
				let res = [];
				// #ifdef H5
				// res = ['wechat', 'alipay'];
				if (this.$wxApi.isweixin()) {
					res = ['wechat']
				} else res = ['alipay']
				// #endif
				// #ifdef MP-WEIXIN
				res = ['wechat']
				// #endif
				// #ifdef APP-PLUS
				res = ['wechat', 'alipay'];
				// #endif
				// #ifdef MP-ALIPAY | MP-TOUTIAO
				res = ['alipay']
				// #endif
				return res
			}
		},
		async onReady() {
			// #ifdef H5
			let payload = this.$route.query.payload
			let openId = uni.getStorageSync('openId')
			if (!openId && payload) {
				payload = JSON.parse(payload)
				if (payload.state = 1 && !openId) {
					let res = await this.$http.post('index.openid', {code: payload.code})
					uni.setStorageSync('openId', res.data)
				}
			}
			if (this.$wxApi.isweixin() && !uni.getStorageSync('openId')) {
				if (!uni.getStorageSync('appId')) {
					let res = await this.$http.post('index.config', {code: ['h5_appId']});
					uni.setStorageSync('appId', res.data.xshop_h5_appid)
				}
				let redirect_uri = encodeURIComponent(this.$wxApi.redirectUrl(location.href))
				let appId = uni.getStorageSync('appId')
				location.href = `https://open.weixin.qq.com/connect/oauth2/authorize?appid=${appId}&redirect_uri=${redirect_uri}&response_type=code&scope=snsapi_base&state=1#wechat_redirect`
			}
			// #endif
		},
		onLoad(options) {
			this.order_sn = options.order_sn
			this.getData()
			
			this.$on('onPaySuccess', (e, res) => {
				this.paySuccess()
			})
			this.$on('onPayFail', (e, res) => {
				uni.showToast({
					title: e,
					'icon': 'none'
				})
			})
		},

		methods: {
			getData() {
				this.$http.get('order.info', {
					order_sn: this.order_sn,
				}).then(res => {
					this.orderInfo = res.data
					this.checkOrder()
				}).catch(e => {})
			},
			//选择支付方式
			changePayType(type) {
				this.payType = type;
			},
			confirm() {
				if (this.payTypeArr.length == 1) this.payType = this.payTypeArr[0]
				// #ifdef H5
				if (this.$wxApi.isweixin()) {
					this.confirmH5WEIXIN()
				} else {
					this.confirmH5Alipay()
				}
				// #endif
				// #ifdef MP-WEIXIN
				this.confirmMPWEIXIN()
				// #endif
				// #ifdef APP-PLUS
				this.confirmApp()
				// #endif
				// #ifdef MP-ALIPAY
				this.confirmMPALIPAY()
				// #endif
				// #ifdef MP-TOUTIAO
				this.confirmMPTOUTIAO()
				// #endif
				
			},
			// #ifdef H5
			//确认支付
			confirmH5WEIXIN() {
				let pay_method = this.$wxApi.isweixin() ? 'mp' : 'wap'
				let form = {
					order_sn: this.order_sn, 
					pay_type: this.payType, 
					pay_method,
					appId: uni.getStorageSync('appId'),
					openId: uni.getStorageSync('openId')
				}
				uni.showLoading()
				this.$http.post('pay', form).then(res => {
					uni.hideLoading()
					if (res.data) {
						let wxpay = typeof res.data == 'string' ? JSON.parse(res.data) : res.data
						if (typeof WeixinJSBridge == "undefined") {
							if (document.addEventListener) {
								document.addEventListener('WeixinJSBridgeReady', this.onBridgeReady(wxpay), false);
							} else if (document.attachEvent) {
								document.attachEvent('WeixinJSBridgeReady', this.onBridgeReady(wxpay));
								document.attachEvent('onWeixinJSBridgeReady', this.onBridgeReady(wxpay));
							}
						} else {
							return this.onBridgeReady(wxpay);
						}
					}
					
				}).catch(e => {uni.hideLoading() })
			},
			onBridgeReady(wxpay) {
				WeixinJSBridge.invoke('getBrandWCPayRequest', {
					appId: wxpay.appId,
					timeStamp: wxpay.timeStamp,
					nonceStr: wxpay.nonceStr,
					package: wxpay.package,
					signType: wxpay.signType,
					paySign: wxpay.paySign
				}, res => {
					if (res.err_msg == "get_brand_wcpay_request:ok") { //支付成功
						this.$emit('onPaySuccess')
						return true;
					} else { //支付失败
						this.$emit('onPayFail', '支付失败')
					}
					return false;
				})
			},
			confirmH5Alipay() {
				let pay_method = 'wap'
				let form = {
					order_sn: this.order_sn, 
					pay_type: this.payType, 
					pay_method
				}
				uni.showLoading()
				this.$http.post('pay', form).then(res => {
					uni.hideLoading()
					var el = document.createElement("div");
					el.setAttribute('id', 'alipaysubmit-box')
					el.innerHTML = res.data;
					document.body.appendChild(el); 
					let form = document.forms['alipaysubmit']
					let data = this.$tools.serializeForm('alipaysubmit')
					let url = form.action
					document.body.removeChild(el)
					window.location.href = url + '&' + data
				})
			},
			// #endif
			
			// #ifdef MP-WEIXIN
			confirmMPWEIXIN() {
				let pay_method = 'miniapp'
				let form = {
					order_sn: this.order_sn, 
					pay_type: this.payType, 
					pay_method,
					appId: uni.getStorageSync('appId'),
					openId: uni.getStorageSync('openId')
				}
				uni.showLoading()
				this.$http.post('pay', form).then(res => {
					uni.hideLoading()
					if (res.data) {
						let wxpay = typeof res.data == 'string' ? JSON.parse(res.data) : res.data
						uni.requestPayment({
							provider: 'wxpay',
							timeStamp: wxpay.timeStamp,
							nonceStr: wxpay.nonceStr,
							package: wxpay.package,
							signType: wxpay.signType,
							paySign: wxpay.paySign,
							success: res => {
								this.$emit('onPaySuccess')
							},
							fail: () => {
								this.$emit('onPayFail')
							},
							complete: () => {}
						});
					}
				}).catch(e => {
					uni.hideLoading()
				})
			},
			// #endif
			
			// #ifdef APP-PLUS
			confirmApp() {
				let pay_method = 'app'
				let form = {
					order_sn: this.order_sn, 
					pay_type: this.payType, 
					pay_method,
					appId: uni.getStorageSync('appId'),
					openId: uni.getStorageSync('openId')
				}
				uni.showLoading()
				this.$http.post('pay', form).then(resData => {
					uni.hideLoading()
					if (resData.data) {
						uni.requestPayment({
							provider: this.payType == 'wechat' ? 'wxpay' : this.payType,
							orderInfo: resData.data,
							success: res => {
								if (res.errMsg == 'requestPayment:ok') {
									this.$emit('onPaySuccess')
								} else {
									this.$emit('onPayFail', '支付失败')
								}
							},
							fail: res => {
								alert(JSON.stringify(res))
								this.$emit('onPayFail', '支付失败')
							}
						})
					}
				}).cathc(e => { uni.hideLoading() })
			},
			// #endif
			
			// #ifdef MP-ALIPAY
			confirmMPALIPAY() {
				let pay_method = 'mp'
				let form = {
					order_sn: this.order_sn, 
					pay_type: this.payType, 
					pay_method,
					appId: uni.getStorageSync('appId'),
					openId: uni.getStorageSync('openId')
				}
				uni.showLoading()
				this.$http.post('pay', form).then(resData => {
					uni.hideLoading()
					if (resData.data) {
						uni.requestPayment({
							provider: this.payType,
							orderInfo: resData.data,
							success: res => {
								if (res.resultCode == 9000) {
									this.$emit('onPaySuccess')
								} else {
									this.$emit('onPayFail', '支付失败')
								}
							},
							fail: res => {
								this.$emit('onPayFail', '支付失败')
							}
						})
					}
				}).catch(e => { uni.hideLoading() })
			},
			// #endif
			// #ifdef MP-TOUTIAO
			confirmMPTOUTIAO() {
				uni.login({
					success: loginRes => {
						uni.showLoading()
						this.$http.post('index.ttmp.login', {code: loginRes.code}).then(res => {
							let openId = res.data
							let form = {
								openId,
								order_sn: this.order_sn,
								pay_type: this.payType,
								pay_method: 'tt'
							}
							this.$http.post('pay', form).then(res => {
								let postData = typeof res.data == 'string' ? JSON.parse(res.data) : res.data
								uni.hideLoading()
								tt.requestPayment({
									data: postData,
									success: res => {
										this.$emit('onPaySuccess')
									},
									fail: err => {
										let data = typeof err.data == 'string' ? JSON.stringify(err.data) : err.data
										let msg = typeof data.response == 'string' ? data.response : JSON.stringify(data.response)
										this.$emit('onPayFail', '支付失败')
										/* uni.showModal({
											title: 'log',
											content: msg,
											showCancel: true,
											cancelText: 'cancel',
											success: e => {}
										}) */
									}
								})
							})
						})
					}
				})
			},
			// #endif
			paySuccess() {
				uni.redirectTo({
					url: '/pages/money/paySuccess'
				})
			},
			navTo(title = '', url = '/pages/order/order?state=0') {
				uni.showToast({
					title,
					icon: 'none'
				})
				setTimeout(() => {
					uni.hideToast()
					uni.redirectTo({
						url
					})
				}, 500)
			},
			checkOrder() {
				if (this.orderInfo.status == -1) {
					this.navTo('订单已失效')
				} else if (this.orderInfo.is_pay == 1) {
					this.navTo("订单已支付")
				}
			},
			reGetData() {
				setTimeout(() => {
					this.getData()
				}, 1000)
			}
		}
	}
</script>

<style lang='scss'>
	.app {
		width: 100%;
	}

	.price-box {
		background-color: #fff;
		height: 265upx;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		font-size: 28upx;
		color: #909399;

		.price {
			font-size: 50upx;
			color: #303133;
			margin-top: 12upx;

			&:before {
				content: '￥';
				font-size: 40upx;
			}
		}
	}

	.pay-type-list {
		margin-top: 20upx;
		background-color: #fff;
		padding-left: 60upx;

		.type-item {
			height: 120upx;
			padding: 20upx 0;
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding-right: 60upx;
			font-size: 30upx;
			position: relative;
		}

		.icon {
			width: 100upx;
			font-size: 52upx;
		}

		.icon-erjiye-yucunkuan {
			color: #fe8e2e;
		}

		.icon19weixinzhifu {
			color: #36cb59;
		}

		.iconalipay {
			color: #01aaef;
		}

		.tit {
			font-size: $font-lg;
			color: $font-color-dark;
			margin-bottom: 4upx;
		}

		.con {
			flex: 1;
			display: flex;
			flex-direction: column;
			font-size: $font-sm;
			color: $font-color-light;
		}
	}

	.mix-btn {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 630upx;
		height: 80upx;
		margin: 80upx auto 30upx;
		font-size: $font-lg;
		color: #fff;
		background-color: $base-color;
		border-radius: 10upx;
		box-shadow: 1px 2px 5px rgba(219, 63, 96, 0.4);
	}
	
	.count-down-box {
		display: flex;
		justify-content: center;
		padding: 20upx 0;
		font-size: $font-base;
		.time {
			color: $base-color;
		}
		
	}
</style>
