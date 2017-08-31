
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>
// +----------------------------------------------------------------------
/**
 * 
 */
// 展开收缩
function  tree_open(obj)
{
	 var tree = $('#list-table tr[id^="1_"], #list-table tr[id^="2_"] '); //,'table-row'
	 if(tree.css('display')  == 'table-row')
	 {
		 $(obj).html("<i class='fa fa-angle-double-down'></i>展开");
		tree.css('display','none');
		$("span[id^='icon_']").removeClass('glyphicon-minus');
		$("span[id^='icon_']").addClass('glyphicon-plus');
	 }else
	 {
		 $(obj).html("<i class='fa fa-angle-double-up'></i>收缩");
		tree.css('display','table-row');
		$("span[id^='icon_']").addClass('glyphicon-minus');
		$("span[id^='icon_']").removeClass('glyphicon-plus');
	 }
}
/**
 * 展开 
 */
function rowClicked(obj)
{
  span = obj;

  obj = obj.parentNode.parentNode;

  var tbl = document.getElementById("list-table");

  var lvl = parseInt(obj.className);

  var fnd = false;
  
  var sub_display = $(span).hasClass('glyphicon-minus') ? 'none' : '' ? 'block' : 'table-row' ;
  //console.log(sub_display);
  if(sub_display == 'none'){
	  $(span).removeClass('glyphicon-minus btn-info');
	  $(span).addClass('glyphicon-plus btn-warning');
  }else{
	  $(span).removeClass('glyphicon-plus btn-info');
	  $(span).addClass('glyphicon-minus btn-warning');
  }

  for (i = 0; i < tbl.rows.length; i++)
  {
      var row = tbl.rows[i];
      
      if (row == obj)
      {
          fnd = true;         
      }
      else
      {
          if (fnd == true)
          {
              var cur = parseInt(row.className);
              var icon = 'icon_' + row.id;
              if (cur > lvl)
              {
                  row.style.display = sub_display;
                  if (sub_display != 'none')
                  {
                      var iconimg = document.getElementById(icon);
                      $(iconimg).removeClass('glyphicon-plus btn-info');
                      $(iconimg).addClass('glyphicon-minus btn-warning');
                  }else{               	    
                      $(iconimg).removeClass('glyphicon-minus btn-info');
                      $(iconimg).addClass('glyphicon-plus btn-warning');
                  }
              }
              else
              {
                  fnd = false;
                  break;
              }
          }
      }
  }

  for (i = 0; i < obj.cells[0].childNodes.length; i++)
  {
      var imgObj = obj.cells[0].childNodes[i];
      if (imgObj.tagName == "IMG")
      {
          if($(imgObj).hasClass('glyphicon-plus btn-info')){
        	  $(imgObj).removeClass('glyphicon-plus btn-info');
        	  $(imgObj).addClass('glyphicon-minus btn-warning');
          }else{
        	  $(imgObj).removeClass('glyphicon-minus btn-warning');
        	  $(imgObj).addClass('glyphicon-plus btn-info');
          }
      }
  }

}

//是否推荐
 (function () {
	this.sort = function (obj, id, name, url) {
		if (!Tool.isNumer(id)) {
			return false;
		}
		value = obj.getAttribute('status') == 0 ? 1 : 0;
		
		var json = {};
		json[name] = value;
		json['id'] = id;
		return Tool.ajax(url, json, function (res) {
			
			if (!res.hasOwnProperty('status') || res.status != 1) {
				layer.msg(res.message);
				return false;
			}
			console.log(value);
			console.log(IMAGE_TYPE[value]);
			obj.setAttribute('src', IMAGE_TYPE[value]);
			obj.setAttribute('status', value == 0 ? 0 : 1);
			return true;
		});
	}
	
	this.sortNumber = function (event, url, id) {
		if (!Tool.isNumer(id)) {
			return false;
		}
		console.log(id);
		var json = {};
		json[event.name] = event.value;
		json['id'] = id;
		console.log(json);
		return Tool.ajax(url, json);
	}
	window.MySort = this;
})(window);
