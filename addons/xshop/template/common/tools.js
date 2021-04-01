import httpConfig from '@/common/request/config';
export default {
	/**
	 * @param {Array}  rows
	 * @param {Object} filter
	 */
	find_rows(rows, filter, return_index = true) {
		for (let i = 0; i < rows.length; i ++) {
			let res = true
			let item = rows[i]
			for (let key in filter) {
				if (item[key] != filter[key]) res = false
			}
			if (res) return return_index ? index : item
		}
		return return_index ? -1 : null
	},
	
	//设置cookie
	setCookie: function (cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
		var expires = "expires=" + d.toUTCString();
		document.cookie = cname + "=" + cvalue + "; " + expires;
		console.info(document.cookie);
	},
	//获取cookie
	getCookie: function (cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') c = c.substring(1);
			if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
		}
		return "";
	},
	//清除cookie
	clearCookie: function (cname) {
		this.setCookie(cname, "", -1);
	},
	getPlatform: function() {
		let platform = uni.getSystemInfoSync().platform;
		
		// #ifdef H5 
		if (window.navigator && window.navigator.userAgent) {
			const ua = window.navigator.userAgent.toLowerCase()
			if (ua.match(/MicroMessenger/i) == 'micromessenger') {
				platform = 'WX-H5'
			} else platform = 'H5'
		}
		// #endif
		// #ifdef MP-WEIXIN
		platform = 'MP-WEIXIN'
		// #endif
		// #ifdef MP-ALIPAY
		platform = 'MP-ALIPAY'
		// #endif
		// #ifdef MP-BAIDU
		platform = 'MP-BAIDU'
		// #endif
		return platform
	},
	has_addon(name) {
		let appInfo = uni.getStorageSync('appInfo')
		if (!appInfo) return false
		if (!appInfo.plugins) return false
		if (appInfo.plugins.indexOf(name) != -1) return true
		return false
	},
	queryStringify(obj) {
		let res = []
		for (let k in obj) {
			res.push(`${k}=${obj[k]}`)
		}
		return res.join('&')
	},
	getQuery(querystring) {
		let arr = querystring.split('?');
		querystring = arr[arr.length - 1];
		arr = querystring.split('&');
		let result = {};
		arr.forEach((item, index) => {
			let v = item.split('=');
			result[v[0]] = v[1];
		})
		console.log(result)
		return result;
	},
	isEmpty(val) {
		if (val == null) return true;
		if (typeof val === 'boolean') return false;
		if (typeof val === 'number') return !val;
		if (val instanceof Error) return val.message === '';
		switch (Object.prototype.toString.call(val)) {
			case '[object String]':
		    case '[object Array]':
		      return !val.length;
			  case '[object File]':
		    case '[object Map]':
		    case '[object Set]': {
		      return !val.size;
		    }
			case '[object Object]': {
		      return !Object.keys(val).length;
		    }
		}
		return false;
	},
	// #ifdef H5
	//获取指定form中的所有的<input>对象  
	getElements(formId) {  
	  var form = document.getElementById(formId);  
	  var elements = new Array();  
	  var tagElements = form.getElementsByTagName('input');  
	  for (var j = 0; j < tagElements.length; j++){ 
	     elements.push(tagElements[j]); 
	  
	  } 
	  return elements;  
	},
	  
	//获取单个input中的【name,value】数组 
	inputSelector(element) {  
	 if (element.checked)  
	   return [element.name, element.value];  
	},
	    
	input(element) {  
	  switch (element.type.toLowerCase()) {  
	   case 'submit':  
	   case 'hidden':  
	   case 'password':  
	   case 'text':  
	    return [element.name, element.value];  
	   case 'checkbox':  
	   case 'radio':  
	    return this.inputSelector(element);  
	  }  
	  return false;  
	},
	  
	//组合URL 
	serializeElement(element) {  
	  var method = element.tagName.toLowerCase();  
	  var parameter = this.input(element);  
	   
	  if (parameter) {  
	   var key = encodeURIComponent(parameter[0]);  
	   if (key.length == 0) return;  
	   
	   if (parameter[1].constructor != Array)  
	    parameter[1] = [parameter[1]];  
	      
	   var values = parameter[1];  
	   var results = [];  
	   for (var i=0; i<values.length; i++) {  
	    results.push(key + '=' + encodeURIComponent(values[i]));  
	   }  
	   return results.join('&');  
	  }  
	 },
	  
	//调用方法   
	serializeForm(formId) {
	  var elements = this.getElements(formId);  
	  var queryComponents = new Array();  
	   
	  for (var i = 0; i < elements.length; i++) {  
	   var queryComponent = this.serializeElement(elements[i]);  
	   if (queryComponent)  
	    queryComponents.push(queryComponent);  
	  }
	   
	  return queryComponents.join('&'); 
	},
	// #endif
}