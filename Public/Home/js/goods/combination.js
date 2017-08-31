// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <13052079525>
// +----------------------------------------------------------------------
// |简单与丰富！让外表简单一点，内涵就会更丰富一点。
// +----------------------------------------------------------------------
// |让需求简单一点，心灵就会更丰富一点。
// +----------------------------------------------------------------------
// |让言语简单一点，沟通就会更丰富一点。
// +----------------------------------------------------------------------
// |让私心简单一点，友情就会更丰富一点。
// +----------------------------------------------------------------------
// |让情绪简单一点，人生就会更丰富一点。
// +----------------------------------------------------------------------
// |让环境简单一点，空间就会更丰富一点。
// +----------------------------------------------------------------------
// |让爱情简单一点，幸福就会更丰富一点。
// +----------------------------------------------------------------------
/**
 * 最佳组合 js
 * @param tagName css选择器
 * @param url     提交链接
 * @author 王强
 */
function Combination(tagName, url)
{
	var _tagName = tagName;
	
	var _url = url;
	
	var obj ;
	
	this.setObj = function () {
		obj = document.getElementsByClassName(_tagName);
		return this;
	}
	
	this.getObj = function() {
		return obj;
	}
	
	this.getTagName  = function() {
		return _tagName;
	}
	
	this.setTageName = function(tagName) {
		_tagName = tagName;
		
		return this;
	}
	
	/**
	 * 加入购物车
	 */
	this.addCart = function () {
		
		this.setObj();
		
		if (!(obj instanceof Object )) {
			toastr.error('加入购物车失败');
			return false;
		}
		
		var goodsId = this.getSelectedCheckBox();
		
		if (Tool.isEmptyArray(goodsId)) {
			toastr.error('请选择商品');
			return false;
		}
		
		return $.post(_url, {data : goodsId}, function (res) {
			 Notify.getInstance(res).NotifyMSG();
		});
		
	}
	/**
	 * 获取选中的商品
	 * @returns Array
	 */
	this.getSelectedCheckBox = function() {
		
		var arr = [];
		var i = 0;
		var length = obj.length;
		for (i = 0; i < length; i++) {
			
			if ( ! obj[i].checked ) {
				continue;
			}
			var json = {
					goods_id : parseInt(obj[i].value),
					price_new : obj[i].getAttribute('price'),
					goods_num : 1,
			};
			
			arr[i] = json;
		}
		return arr;
	}
	
}
// 实例化对象
var comb = new Combination('com_b', COM_URL);
var comR = new Combination('com_r', COM_URL)