<template>
	<x-popup ref="$popup">
		<view class="e_info" v-if="!loading && !error">
			<view class="e_name">					
				{{order.express && order.express.name}}
			</view>
			<view class="e_code">
				{{order.express_no}}
			</view>
		</view>
		<view class="list" v-if="!loading && !error">
			<view class="item" v-for="(item, index) in content" :key="index">
				<text>{{item.content}}</text>
				<text>{{item.time}}</text>
				<text class="ic"><text class="i"></text></text>
			</view>
		</view>
		<view class="loading" v-if="loading">
			加载中……
		</view>
		<view class="error" v-if="error">
			{{error}}
		</view>
	</x-popup>
</template>

<script>
	import xPopup from '../../../components/x-popup.vue';
	export default {
		components: { xPopup },
		data() {
			return {
				content: [],
				loading: true,
				error: null
			}
		},
		props: {
			order: {}
		},
		methods: {
			open() {
				this.loading = true
				this.error = null;
				this.$http.post('order.express', {order_sn: this.order.order_sn}).then(res => {
					this.loading = false
					this.content = res.data;
				}).catch(res => {
					this.error = res.msg
					this.loading = false
				})
				this.$refs.$popup.open();
			},
			close() {
				this.$set(this, 'content', [])
				this.error = null
				this.$refs.$popup.close();
			}
		}
	}
</script>

<style lang="scss">
	.list {
		display: flex;
		flex-direction: column;
		.item {
			display: flex;
			flex-direction: column;
			border-left: 1px solid #dcdcdc;
			padding-bottom: 10px;
			padding-left: 15px;
			position: relative;
			color: #888;
			align-items: flex-start!important;
			.ic {
				display: block;
				position: absolute;
				left: -16upx;
				top: 6upx;
				width: 16px;
				height: 16px;
				background-color: #dcdcdc;
				border-radius: 38upx;
				.i {
					display: block;
					width: 12px;
					height: 12px;
					border-radius: 6px;
					position: absolute;
					left: 2px;
					top: 2px;
				}
			}
			&:first-child {
				color: #00bb42;
				.ic {
					background-color: #b0ffd4;
				}
				.i {
					background-color: #00aa2c;
				}
			}
			text:first-child {
				font-size: $font-base + 4;
				margin-bottom: 10upx;
			}
		}
		
	}
	.e_info {
			display: flex;
			margin-bottom: 16upx;
			color: #333;
			.e_code {
				margin-left: 10upx;
			}
		}
	.loading {
		display: flex;
		justify-content: center;
		padding: 20upx 0;
		color: #888;
	}
	.error {
		display: flex;
		justify-content: center;
		padding: 20upx 0;
		color: #555;
	}
</style>
