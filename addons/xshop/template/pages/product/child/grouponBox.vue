<template>
	<view class="groupon-box" v-if="groups.length">
		<view class="title">
			<text>{{count}}人在拼团，可直接参与</text>
			<text class="more">
				<!-- <text class="yticon icon-you"></text> -->
			</text>
		</view>
		<view class="list">
			<view class="item" v-for="(item, i) in groups" :key="i">
				<view class="user-info-box">
					<!-- #ifdef H5 -->
					<img class="avatar" :src="item.user.avatar || '/h5/static/missing-face.png'"></img>
					<!-- #endif -->
					<!-- #ifndef H5 -->
					<image class="avatar" :src="item.user.avatar || '/static/missing-face.png'"></image>
					<!-- #endif -->
					<text>{{item.user.nickname}}</text>
				</view>
				<view class="group-info">
					<view class="info-text">
						<text>还差{{groupNumber - item.group_members.length}}人拼成</text>
						<count-down @callback="init" :targetTime="parseInt(item.left_time)"></count-down>
					</view>
					<view class="info-op">
						<view class="btn btn-warn" @click="buy(item)">
							去拼团
						</view>
					</view>
				</view>
			</view>
		</view>
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
				groups: [],
				count: 0,
				group_timeout: 0
			}
		},
		props: {
			value: {
				type: null
			},
			groupNumber: {
				type: null
			},
			product: {
				type: Object
			}
		},
		watch: {
			product: {
				handler(val) {
					this.init()
				},
				deep: true
			}
		},
		mounted() {
			this.init()
		},
		methods: {
			init() {
				if (this.$tools.has_addon('xshopgroupon') && this.product.groupon) this.getGrouponGroups()
			},
			getGrouponGroups() {
				let form = {
					product_id: this.value
				}
				this.$http.post('groupon.groups', form).then(res => {
					this.groups = res.data.list
					this.count = res.data.count
					this.group_timeout = res.data.group_timeout
				})
			},
			buy(item) {
				let data = {
					groupon_id: item.id,
					order_type: 1
				}
				this.$emit('add-group', data)
			}
		}
	}
</script>

<style lang="scss">
	.groupon-box {
		font-size: $font-base;
		background: #fff;
		margin: 20upx 0;
		padding: 20upx 30upx;

		.title {
			display: flex;
			justify-content: space-between;
			font-size: $font-base + 4upx;
			margin-bottom: 20upx;

			.more {
				font-size: $font-base;
				color: $font-color-dark;
			}
		}

		.list {
			display: flex;
			flex-direction: column;

			.item {
				display: flex;
				justify-content: space-between;
				margin: 4upx 0;
				width: 100%;

				.user-info-box {
					display: flex;
					align-items: center;

					.avatar {
						width: 80upx;
						height: 80upx;
						margin-right: 20upx;
						border-radius: 50%;
					}
				}

				.group-info {
					display: flex;
					align-items: center;

					.info-text {
						display: flex;
						flex-direction: column;
					}

					.info-op {
						margin-left: 20upx;
					}
				}

				.btn {
					padding: 10upx 15upx;

				}

				.btn-warn {
					background: #fa436a;
					border-radius: 10upx;
					color: #fff;

				}
			}
		}
	}
</style>
