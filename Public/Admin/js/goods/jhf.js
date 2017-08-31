
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

树形组织框架列表 
v1.0 
2011年1月6日 
作者：flycrosscloud 
********************************/ 
//初始化框架 
var allImages = { 
HasNodeClose: "<img src='image/ftv2pnode.gif'/>", //包含子节点，闭合状态（不是最后一个) 
HasNodeOpen: "<img src='image/ftv2mnode.gif'/>", //包含子节点，打开状态（不是最后一个） 
LastHasNodeClose: "<img src='image/ftv2plastnode.gif'/>", //包含子节点，闭合状态（最后一个） 
LastHasNodeOpen: "<img src='image/ftv2mlastnode.gif'/>", //包含子节点，打开状态（最后一个） 
CommonNode: "<img src='image/ftv2node.gif'/>", //不包含子节点，普通节点（不是最后一个） 
LastCommonNode: "<img src='image/ftv2lastnode.gif'/>", //不包含子节点，普通节点（最后一个） 
NodeLine: "<img src='image/ftv2vertline.gif'/>", //节点间连线 
NodeClose: "<img src='image/department.gif'/>", //节点关闭状态 
NodeOpen: "<img src='image/departmentopen.gif'/>", //节点打开状态 
NodeBlank: "<img src='image/ftv2vertlineblank.gif'/>"//空白连线 
}; 
$(function () 
{ 
$.post("http://localhost/system/asmx/wsTree.asmx/HelloWorld", function (data) { InitTree(data); }); 
}); 
