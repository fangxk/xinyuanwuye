import http  from '@/common/http'
import Vue from 'vue'
const state = {
	count: 0
}


const mutations = {
	SAVE_MESSAGE_COUNT(state, data) {
		state.count = data.total;
	}
}

const actions = {
	getMessageCount({ commit }, form = {}) {
		return new Promise((resolve, reject) => {
			http.post('message.newMsgCount', form).then(res => {
				commit('SAVE_MESSAGE_COUNT', res.data)
				resolve(res)
			}).catch(e => { reject(e) })
		})
	}
}

export default {
	state,
	mutations,
	actions
}