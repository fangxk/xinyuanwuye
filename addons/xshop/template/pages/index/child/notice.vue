<template>
	<view class="">
		<notice-bar :bg-color="(item.params && item.params.background) || '#fffbe8'" :color="(item.params && item.params.color) || '#de8c17'" :text="text" @click="handleClick" @change="handleChange"></notice-bar>
	</view>
</template>

<script>
	import { uniNoticeBar } from '@dcloudio/uni-ui'
	import noticeBar from '@/components/an-notice-bar.vue';
	export default {
		components: { uniNoticeBar, noticeBar },
		data() {
			return {
				item: {}
			}
		},
		props: {
			list: {
				type: Object|Array,
				default: []
			} 
		},
		computed: {
			text() {
				let res = [];
				this.list.forEach((item, index) => {
					res.push(item.title)
				})
				return res.join('|');
			}
		},
		methods: {
			handleClick(index, text) {
				let item = this.list[index]
				this.$meRouter({type: item.type, target: item.target})
			},
			handleChange(res) {
				this.item = this.list[res.detail.current]
			}
		}
		
	}
</script>

<style>
</style>
