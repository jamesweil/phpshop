/**
 * js 监听事件
 */
(function (w){
	
	this.callBackList = {};
	
	this.isParseParam = false;
	
	this.isMerge = true;// 是否合并
	
	this.receive = '';
	
	this.paramType = {
	     0 : this.parseString,
	};
	
	this.setParamType = function (type, value) {// 设置类型
		if (this.paramType.hasWonProperty(type)) {// 已存在
			return false;
		}
		this.paramType[type] = value;
	}
	
	this.getParamType  = function () {
		return this.paramType;
	}
	
	this.insertListen = function(name, struct) {
		var list = this.callBackList;
		if (!this.isMerge && list.hasOwnProperty(name)) {
			console.log('已存在该标签');
			return ;
		}
		
		list[name] = struct;
		
		this.callBackList = list;
	},
	
	/**
	 * 获取json长度
	 */
	this.getJsonObjLength = function() {
         var Length = 0;
         var jsonObj = this.paramType;
         for (var item in jsonObj) {
            Length++;
         }
         return Length;
     }
	
	this.listen = function (name, param, typeFunction) {
		var list = this.callBackList;
		
		if (!list.hasOwnProperty(name)) {
			return null;
		}
		
		if (this.isParseParam && !typeFunction && typeof(typeFunction) !== 'function') {
			throw new Error('不能存在 该函数【'+typeFunction+'】');
		}
		if (this.isParseParam) {
			param = typeFunction(param);// 处理参数
		}
		return eval("list."+name+"(param)");
	}
	/**
	 * 数组解析
	 * 
	 * @param Array
	 *            param
	 * @return string;
	 */
	this.parseParamArray = function (param) {
		
		if (!(param instanceof Array)) {
			throw new Error('参数类型不匹配，请传递数组类型');
		}
		var str = '';
		var length = this.getJsonObjLength();
		for (var i in param) {
			str += ','+ param[i];
		}
		console.log(str);
		str = str.substring(1);
		
		return str;
		
	}
	
	this.parseString = function (param) {
		console.log(param);
		if (typeof(param) !== 'String') {
			throw new Error('参数类型不匹配，请传递字符串类型');
		}
		console.log(param);
		return "'"+param+'"';
	}
	
	w.EventAddListener = this;
	
})(window);