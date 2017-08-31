
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
 * 上传图片 后台专用
 * @access  public
 * @null int 一次上传图片张图
 * @elementid string 上传成功后返回路径插入指定ID元素内
 * @path  string 指定上传保存文件夹,默认存在Public/upload/temp/目录
 * @callback string  回调函数(单张图片返回保存路径字符串，多张则为路径数组 )
 */
function GetUploadify(num,elementid,path,callback)
{
    var upurl ='/adminprov.php?m=Admin&c=Uploadify&a=upload&num='+num+'&input='+elementid+'&path='+path+'&func='+callback;
    var iframe_str='<iframe frameborder="0" ';
    iframe_str=iframe_str+'id=uploadify ';
    iframe_str=iframe_str+' src='+upurl;
    iframe_str=iframe_str+' allowtransparency="true" class="uploadframe" scrolling="no"> ';
    iframe_str=iframe_str+'</iframe>';
    $("body").append(iframe_str);
    $("iframe.uploadframe").css("height",$(document).height()).css("width","100%").css("position","absolute").css("left","0px").css("top","0px").css("z-index","999999").show();
    $(window).resize(function(){
        $("iframe.uploadframe").css("height",$(document).height()).show();
    });
}

EventAddListener.insertListen('comboSelect', function (param) {
	var obj = param[1];
	var str = param[0];
	console.log(str);
	$(obj).html(null);
	$(obj).html(str);
	$(obj).comboSelect();//下拉选择框 加事件
});

$(function(){
    //回显商品分类显示
    $('.status').val(status_value);
	Tool.getClassById(CLASS_GET_LIST, document.getElementById('first'));
    //图片显示
    $("#category_img").css("display","none");

});
