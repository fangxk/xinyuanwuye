import http  from '@/common/http'
import Vue from 'vue'
const state = {
	config: null
}


const mutations = {
	SAVE_SIGN_PARAMS(state, data) {
		state.config = data;
	}
}

const actions = {
	getShareSignParams({ commit }, form){
		return new Promise((resolve, reject) => {
			http.get('vendor.getSignParams', {url: window.location.href.split('#')[0]}).then(res => {
				commit('SAVE_SIGN_PARAMS', res.data);
				resolve(res.data);
			})
		})
	}
}

export default {
	state,
	mutations,
	actions
}