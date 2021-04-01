<template>
	<uni-popup ref="$popup" type="center" class="after-sale-box border-radius t-popup">
		<view class="container">
			<view class="title">
				<text>申请退款</text>
			</view>
			<view class="list">
				<view class="item">
					<view class="item-title">
						<text>备注</text>
					</view>
					<view class="item-content">
						<input class="input" type="text" v-model="form.remark" placeholder-class="input-placeholder" placeholder="请与商家进行协商" />
					</view>
				</view>
			</view>
			<view class="btns">
				<view class="btn btn-cancel" @click="close">
					取消
				</view>
				<view class="btn btn-primary" @click="submit">
					确定
				</view>
			</view>
		</view>
	</uni-popup>
</template>

<script>
	import {uniPopup} from '@dcloudio/uni-ui'
	export default {
		data() {
			return {
				form: {
					remark: ''
				}
			}
		},
		components: {
			uniPopup
		},
		props: {
			value: {
				type: null
			}
		},
		watch: {
		},
		methods: {
			submit() {
				uni.showLoading()
				let form = Object.assign({order_sn: this.value}, this.form)
				this.$http.post('order.apply_refund', form).then(res => {
					uni.hideLoading()
					this.close()
					this.$emit('callback')
				}).catch(e => {
					uni.hideLoading()
				})
			},
			open() {
				this.$refs.$popup.open()
			},
			close() {
				this.$refs.$popup.close()
			}
		}
	}
</script>

<style lang="scss">
	.after-sale-box {
		font-size: $font-base;
		.container {
			width: calc(80vw - 60upx);
			.title {
				display: flex;
				justify-content: center;
				font-size: $font-base + 2upx;
				margin: 10upx 0;
			}
			.list {
				margin-top: 20upx;
				.item {
					display: flex;
					flex-direction: row!important;
					justify-content: space-between;
					align-items: center!important;
					margin: 10upx 0;
					border: none!important;
					.radio-group {
						display: flex;
						margin: 20upx 0;
						.radio {
							display: flex;
							align-items: center;
						}
					}
					.item-title {
						display: flex;
						width: 120upx;
						justify-content: center;
					}
					.item-content {
						flex: 1;
						.input {
							padding: 0 10upx;
							border: 1px #ddd solid;
							border-radius: 10upx;
							height: 64upx;
						}
						.input-placeholder {
							font-size: $font-base;
						}
					}
				}
			}
	
			.btns {
				display: flex;
				justify-content: space-between;
				margin-top: 30upx;
				.btn {
					display: flex;
					justify-content: center;
					align-items: center;
					height: 70upx;
					flex: 1;
					font-size: $font-base + 2upx;
				}
				.btn-cancel {
					color: $font-color-light;
				}
				.btn-primary {
					color: $base-color;
				}
			}
		}
	}
	.t-popup {
		.uni-popup {
			color: red;
		}
		.uni-popup__wrapper-box {
			border-radius: 20upx;
		}
	}
	.t-popup /deep/ .uni-popup__wrapper-box {
		border-radius: 20upx;
	}
</style>
