<template>
	<uni-popup ref="$x_popup" type="center" class="after-sale-box border-radius t-popup">
		<view class="container">
			<slot></slot>
		</view>
	</uni-popup>
</template>

<script>
	import { uniPopup } from '@dcloudio/uni-ui'
	export default {
		components: { uniPopup },
		methods: {
			open() {
				this.$refs.$x_popup.open();
			},
			close() {
				this.$refs.$popup.close();
			}
		}
	}
</script>

<style lang="scss">
	.container {
		font-size: $font-base;
		width: calc(80vw - 60upx);
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
