<template>
	<view class="container">
		<list-cell title="修改昵称" @eventClick="edit('修改昵称', 'nickname')"></list-cell>
		<list-cell title="修改密码" @eventClick="edit('修改密码', 'password')"></list-cell>
		<prompt ref="$prop" :title="title" @submit="submit"></prompt>
	</view>
	
</template>

<script>
	import {  
	    mapMutations, mapActions, mapState
	} from 'vuex';
	import listCell from '@/components/mix-list-cell';
	import prompt from './child/prompt'
	export default {
		components: {
			prompt, listCell
		},
		data() {
			return {
				title: '',
				key: ''
			};
		},
		computed: {
			...mapState({
				userinfo: state => state.user.userinfo
			})
		},
		methods:{
			...mapMutations(['SAVE_USERINFO']),
			edit(title, key){
				this.title = title
				this.key = key
				this.$refs.$prop.open()
			},
			submit(val) {
				let form = {}
				form[this.key] = val
				this.$http.post('user.info.edit', form).then(res => {
					this.$refs.$prop.close()
					uni.showToast({
						title: '修改成功'
					})
					this.SAVE_USERINFO(res.data)
				})
			},
			navTo(url) {
				uni.navigateTo({
					url
				})
			}
		}
	}
</script>

<style lang='scss'>
	page{
		background: $page-color-base;
	}
	.container {
		margin-top: 10upx;
		background: #fff;
	}
	.list-cell{
		display:flex;
		align-items:baseline;
		padding: 20upx $page-row-spacing;
		line-height:60upx;
		position:relative;
		background: #fff;
		justify-content: center;
		&.log-out-btn{
			margin-top: 40upx;
			.cell-tit{
				color: $uni-color-primary;
				text-align: center;
				margin-right: 0;
			}
		}
		&.cell-hover{
			background:#fafafa;
		}
		&.b-b:after{
			left: 30upx;
		}
		&.m-t{
			margin-top: 16upx; 
		}
		.cell-more{
			align-self: baseline;
			font-size:$font-lg;
			color:$font-color-light;
			margin-left:10upx;
		}
		.cell-tit{
			flex: 1;
			font-size: $font-base + 2upx;
			color: $font-color-dark;
			margin-right:10upx;
		}
		.cell-tip{
			font-size: $font-base;
			color: $font-color-light;
		}
		switch{
			transform: translateX(16upx) scale(.84);
		}
	}
</style>
