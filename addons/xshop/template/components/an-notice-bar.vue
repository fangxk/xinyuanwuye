<template>
	<view>
		<view class="an-notice-box" :style="'background-color: '+bgColor+';'">
			<view class="an-notice-icon">
				  <text class="iconfont iconsound" :style="{color}"></text>
			</view>
			<scroll-view class="an-notice-content">
				<swiper @change="handleChange" v-if="show" class="swiper" :autoplay="true" :interval="switchTime*1000" :duration="1500" :circular="true" :vertical="true">
					<swiper-item v-for="(text, index) in list" :key="index" class="an-notice-content-item" @click="handleClick(index, text)">
						<view class="swiper-item">
							<text class="an-notice-content-item-text" :style="'color: '+color+';'">
								<text v-if="list.length > 1 && showSerial">{{index+1+'. '}}</text>
								{{text}}
							</text>
						</view>
					</swiper-item>
				</swiper>
			</scroll-view>
		</view>
	</view>
</template>

<script>
	import uniIcons from '@/components/uni-icon/uni-icon.vue'
	export default {
		components: {
			uniIcons
		},
		props:{
			text: {
				type: String,
				default: '暂无未读消息'
			}, 
			color: {
				type: String,
				default: '#de8c17'
			},
			bgColor: {
				type: String,
				default: '#fffbe8'
			},
			switchTime: {
				type: Number,
				default: 3
			},
			showSerial: {
				type: Boolean,
				default: false
			}
		},
		data() {
			return {
				number: 0,
				list: [],
				copyText: '',
				show: '',
			};
		},
		mounted() {
			this.list = this.text.split('|');
			this.show = true;
			/* this.number = 0;
			this.startMove(); */
		},
		watch: {
			text: function () {
				this.show = true;
				if(this.text != this.copyText){
					this.copyText = this.text;
					this.list = this.text.split('|');
				}
				/* this.number = 0;
				this.startMove(); */
			}
		},
		methods: {			
			/* startMove () {
			  // eslint-disable-next-line
			  let timer = setTimeout(() => {
				if (this.number === this.list.length-1) {
				  this.number = 0;
				} else {
				  this.number += 1;
				}
				this.startMove();
			  }, this.switchTime*1000);
			}, */
			more(){
				this.show = false;
				this.$emit('more')
			},
			handleClick(index, text) {
				this.$emit('click', index, text)
			},
			handleChange(res) {
				this.$emit('change', res)
			}
		}
	}
</script>

<style>
	.swiper{
		height: 60upx!important;
	}
	.an-notice-box{
		width: 100%; 
		height: 60upx; 
		padding: 0 10upx; 
		overflow: hidden; 
		margin: 20upx 0; 
		display: flex; 
		justify-content: flex-start;
	}
	.an-notice-icon{
		width: 60upx; 
		height: 60upx; 
		line-height: 50upx; 
		text-align: center; 
		position: relative;
	}
	.an-notice-content{
		width: calc(100% - 40upx); 
		position: relative; 
		font-size: 14px;
	}
	.an-notice-content-item{
		width: 100%; 
		height: 60upx; 
		text-align: left; 
		line-height: 60upx;
	}
	.an-notice-content-item-text{
		display: block; 
		white-space: nowrap; 
		text-overflow: ellipsis; 
		overflow: hidden;
	}
	.an-notice-more{
		width: 130upx; 
		height: 60upx; 
		font-size: 12px; 
		line-height: 60upx; 
		text-align: right; 
		color: #999;
	}
	
	@keyframes anotice {
		 0%  {transform: translateY(100%);}
	    30%  {transform: translateY(0);}
	    70%  {transform: translateY(0);}
	   100%  {transform: translateY(-100%);}
	}
</style>
