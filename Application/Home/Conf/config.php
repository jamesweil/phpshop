<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>
// +----------------------------------------------------------------------

defined('Server') or define('Server', '');
define('__STATIC_MOUDLE__', 'Home');
return array(
	//'配置项'=>'配置值'
    'LOAD_EXT_CONFIG'    => COMMON_PATH.'Conf/tmpl.php', // 加载配置文件
	//表单令牌
	'form' => 'abc15689iuoiuhkjvg',
     ////江浙沪皖包邮
    'free' => array('江苏省','浙江省','上海市','安徽省'),
    /* 头像图片上传相关配置 */
    'USER_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/user/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //图片上传相关配置（文件上传类配置）
    'USER_HEADER'       => '/Uploads/user/',
    'SHOW_PAGE_TRACE'   => false,
    'URL_HTML_SUFFIX'   =>'.html',
    'promotion_type'    => [ //0 打折，1,减价优惠,2,固定金额出售
        -1 => 'Home\Strategy\SpecificStrategy\BuySendVouchersActivity',
        'Home\Strategy\SpecificStrategy\DiscountPromotionsActivity',
        'Home\Strategy\SpecificStrategy\DiscountActivity',
        'Home\Strategy\SpecificStrategy\FixedAmountSaleActivity',
        1000000 => 'Home\Strategy\SpecificStrategy\NoActivePrice'
     ],
    
    'pay_type_img' => [
      '/Public/Home/img/wx.png',
      '/Public/Home/img/alipay.png',
      '/Public/Home/img/union_pay.png',
      '/Public/Home/img/balance.png'
    ],
    
    'internetTitle' => [
        'goodsDetail' => '商品详情',
        'goodsList'   => '商品列表',
        'orderList'   => '订单列表',
        'orderCenter' => '订单中心',
        'index'       => '首页',
        'login'       => '登录',
        'register'    => '注册',
        'settlement'  => '结算页',
        'search'      => '搜索'
    ],
    'activity_type' => [
        '没有活动',
        '尾货清仓',
        '最新促销',
        '积分商城',
        '打印耗材'
    ],
    'ad_space_id' => 38, //首页楼层商品中间大图
    "ERROR_PAGE"=>'/Home/Index/404.html',
    "MY_TRACKS_COOKIE_KEY"=>'MY_TRACKS',//保存我的足迹
    "ANNOUNCE_PAGE"=>'2',//公告列表页
    'SHOW_PAGE_TRACE' => true,
    'welcome' => '欢迎来到亿速网络！',
);