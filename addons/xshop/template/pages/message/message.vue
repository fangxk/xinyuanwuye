<template>
	<view>
		<view class="head">
			<uni-segmented-control v-if="items.length>1" :red-dot="redDots" :current="current" :values="items" @clickItem="onClickItem" style-type="button" active-color="#ec605f"></uni-segmented-control>
		</view>
		<view class="item" v-for="(item, index) in articles" :key="index" @tap="onMessageClick(item)">
			<text class="time">{{item.create_time}}</text>
			<view class="content">
				<text class="title">{{item.title}}</text>
				<view class="img-wrapper" v-if="item.image">
					<image class="pic" :src="item.image"></image>
				</view>
				<text class="description">
					{{item.description}}
				</text>
				<view class="bot b-t">
					<text>查看详情</text>
					<text class="more-icon iconfont iconyou"></text>
				</view>
			</view>
		</view>
		<view class="loading" v-if="loading">
			加载中……
		</view>
		
		<view class="no-more" v-if="!hasMore">
			没有了……
		</view>
		<x-popup ref="msg_box">
			<rich-text class="msg-box" :nodes="msgNodes"></rich-text>
		</x-popup>
	</view>
</template>

<script>
	import uniSegmentedControl from '@/components/uni-segmented-control'
	import empty from "@/components/empty";
	import xPopup from '@/components/x-popup'
	export default {
		components: {uniSegmentedControl, empty, xPopup},
		data() {
			return {
				categories: [],
				articles: [],
				hasMore: false,
				current: 0,
				loading: false,
				page: 1,
				redDots: [],
				msgNodes: []
			}
		},
		onLoad() {
			this.getCategories()
		},
		computed: {
			items() {
				let res = [];
				this.categories.forEach((item, index) => {
					res.push(item.title)
				})
				return res;
			}
		},
		methods: {
			onClickItem({ currentIndex }) {
				this.current = currentIndex
				this.$set(this.redDots, currentIndex, false)
				this.reload()
			},
			reload() {
				this.page = 1;
				this.hasMore = true;
				this.articles = [];
				this.getArticles()
			},
			async getCategories() {
				let res = await this.$http.get('msg.categories');
				this.categories = res.data;
				this.redDots = this.getRedDots(res.data)
				this.getArticles()
			},
			async getArticles() {
				this.loading = true
				this.hasMore = true
				let res = await this.$http.get('msg.articles', {id: this.categories[this.current].id, page: this.page});
				this.loading = false
				this.articles = this.articles.concat(res.data.data)
				if (res.data.current_page < res.data.last_page) {
					this.hasMore = true;
					this.page ++;
				}
				else this.hasMore = false;
			},
			onMessageClick(item) {
				if (this.current == 0) {
					switch (parseInt(item.msg_type)) {
						case -1: {
							this.msgNodes = this.$parseHtml(item.target);
							this.$refs.msg_box.open();
							break;
						}
						default: {
							this.$meRouter({type: item.msg_type, target: item.target});
							break;
						}
					}
				} else this.$meRouter({type: 5, target: item.id})
			},
			getRedDots(data)
			{
				let res = [];
				data.forEach((item, index) => {
					if (item.count) res.push(true);
					else res.push(false)
				})
				return res;
			}
		},
		onPullDownRefresh() {
			this.reload();
		},
		onReachBottom() {
			if (this.hasMore) this.getArticles()
		}
	}
</script>

<style lang='scss'>
	page {
		background-color: #f7f7f7;
		padding-bottom: 30upx;
	}
	
	.head {
		width: 80vw;
		margin: auto;
		margin-top: 8upx;
	}

	.item {
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.time {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 80upx;
		padding-top: 10upx;
		font-size: 26upx;
		color: #7d7d7d;
	}

	.content {
		width: 710upx;
		padding: 0 24upx;
		background-color: #fff;
		border-radius: 4upx;
	}

	.title {
		display: flex;
		align-items: center;
		height: 90upx;
		font-size: 32upx;
		color: #303133;
	}

	.img-wrapper {
		width: 100%;
		height: 260upx;
		position: relative;
	}

	.pic {
		display: block;
		width: 100%;
		height: 100%;
		border-radius: 6upx;
	}

	.cover {
		display: flex;
		justify-content: center;
		align-items: center;
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(0, 0, 0, .5);
		font-size: 36upx;
		color: #fff;
	}

	.description {
		display: inline-block;
		padding: 16upx 0;
		font-size: 28upx;
		color: #606266;
		line-height: 38upx;
	}

	.bot {
		display: flex;
		align-items: center;
		justify-content: space-between;
		height: 80upx;
		font-size: 24upx;
		color: #707070;
		position: relative;
	}

	.more-icon {
		font-size: 32upx;
	}
	.loading, .no-more {
		text-align: center;
		color: #888;
		font-size: $font-base;
		margin: 30upx 0 10upx 0;
	}
	.msg-box {
		word-break: break-word;
	}
</style>
