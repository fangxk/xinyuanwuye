<template>
	<view class="">
		<x-popup ref="xp">
			<image v-if="url" @load="onLoadHandle" @error="onErrorHandle" :src="url" mode="widthFix" style="width:100%;"></image>
			<view class="box">
				<!-- #ifdef H5 -->
				<text>请长按保存图片</text>
				<!-- #endif -->
				<!-- #ifdef MP||APP-PLUS -->
				<view class="btn btn-primary btn-sm" @tap="save">保存图片</view>
				<!-- #endif -->
			</view>
		</x-popup>
	</view>
</template>

<script>
	import xPopup from '@/components/x-popup.vue';
	import config from '@/common/request/config';
	import { mapState } from 'vuex';
	export default {
		components: {
			xPopup
		},
		data() {
			return {
				url: null,
				isLoaded: false
			}
		},
		props: {
			pid: null,
			sku_id: null
		},
		computed: {
			...mapState({
				user: state => state.user.userinfo
			})
		},
		methods: {
			open() {
				setTimeout(() => {
					if (!this.isLoaded) {
						uni.showLoading({
							title: '加载中……'
						})						
					}
				}, 300)
				this.url = config.baseUrl + 'xshopshare/index/getShareImage?id=' + this.pid + '&sku_id=' + this.sku_id + '&platform=' + this.$tools.getPlatform();
				if (this.user && this.user.id) this.url += '&uid=' + this.user.id;
				this.$refs.xp.open();
			},
			close() {
				this.$refs.xp.close();
			},
			onLoadHandle() {
				this.isLoaded = true;
				uni.hideLoading()
			},
			onErrorHandle() {
				this.isLoaded = true;
				uni.showToast({
					title: '加载失败，请重试',
					icon: 'none'
				})
			},
			save() {
				uni.showToast({
					title: '下载中……',
					icon: 'loading'
				})
				uni.downloadFile({
					url: this.url,
					success: res => {
						uni.hideToast()
						if (res.statusCode === 200) {
							uni.saveImageToPhotosAlbum({
								filePath: res.tempFilePath,
								success: ret => {
									uni.showToast({
											title: '保存成功'
									})
								}
							})
						}
					},
					error: e => {
						uni.showToast({
							title: '保存失败',
							icon: none
						})
					}
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	.btn {
		position: relative;
		display: block;
		margin-left: auto;
		margin-right: auto;
		padding-left: 28px;
		padding-right: 28px;
		box-sizing: border-box;
		font-size: 36px;
		text-align: center;
		text-decoration: none;
		line-height: 2.55555556;
		border-radius: 10px;
		-webkit-tap-highlight-color: transparent;
		overflow: hidden;
		color: #000;
		background-color: #f8f8f8;
	}
	.btn-sm {
		height: 60rpx;
		line-height: 60rpx;
		font-size: 28rpx;
	}
	.btn-primary {
		background: #fa436a;
		color: #fff;
	}
	.box {
		display: flex;
		justify-content: center;
		.text {
			color: #555;
		}
		.btn {
			width: 100%;
		}
	}
</style>
