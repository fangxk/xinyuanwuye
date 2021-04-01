<template>
	<uni-popup ref="$unipopup" :mask-click="true" mode="normal" class="t-popup" type="center">
		<view class="container">
			<view class="content">
				<view class="msgbox-content">
					<text>{{title}}</text>
				</view>
				<view class="msgbox-input">
					<input class="input" type="text" v-model="data" />
				</view>
			</view>
			<view class="btns">
				<button class="btn btn-cancel" @click="close">取消</button>
				<button class="btn btn-submit" @click="submit">确定</button>
			</view>
		</view>
		
	</uni-popup>
</template>

<script>
	import { uniPopup } from '@dcloudio/uni-ui'
	export default {
		components: {
			uniPopup
		},
		data() {
			return {
				data: ''
			}
		},
		props: {
			title: {
				type: String
			}
		},
		mounted() {
			this.visible = this.value
		},
		methods: {
			submit() {
				let data = this.data
				this.$emit('submit', data)
				this.data = ''
			},
			close() {
				this.$refs.$unipopup.close()
			},
			open() {
				this.$refs.$unipopup.open()
			}
		}
	}
</script>

<style lang="scss" scoped>
	.container {
		width: calc(80vw - 60upx);
	}
	.content {
		padding: 0upx 30upx;
		border-radius: 10upx;
		.msgbox-content {
			display: flex;
			justify-content: center;
			margin: 20upx 0;
			color: #404245;
			font-size: 14px;
			line-height: 20px;
		}
		.msgbox-input {
			margin: 30upx 0;
			.input {
				height: 70upx;
				line-height: 70upx;
				border: 1px solid #dedede;
				border-radius: 10upx;
				padding: 10upx;
				width: 100%;
			}
		}
	}
	.btns {
		display: flex;
		height: 80upx;
		line-height: 80upx;
		.btn {
			    line-height: 80upx;
				display: block;
				flex: 1;
				background-color: #fff!important;
				border: none!important;
				margin: 0;
				border: 0;
				&::after {
					border: none;
					border-radius: none;
				}
		}
		
		.btn-cancel {
			font-size: 16px;
			color: #404245;
			
		}
		.btn-submit {
			font-size: 16px;
			color: #fa800a;
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
