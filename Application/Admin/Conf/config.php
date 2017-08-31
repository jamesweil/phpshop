<?php
define('UPLOAD_PATH', 'Uploads/');
define('__STATIC_MOUDLE__', 'Admin');
return array(
    'LOAD_EXT_CONFIG' => COMMON_PATH . 'Conf/tmpl.php', // 加载配置文件
    /* 商品图片上传相关配置 */
    'GOODS_UPLOAD' => array(
        'mimes' => '', // 允许上传的文件MiMe类型
        'maxSize' => 2 * 1024 * 1024, // 上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,jpeg', // 允许上传的文件后缀
        'autoSub' => true, // 自动子目录保存文件
        'subName' => array(
            'date',
            'Y-m-d'
        ), // 子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/goods/', // 保存根路径
        'savePath' => '', // 保存路径
        'saveName' => array(
            'uniqid',
            ''
        ), // 上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', // 文件保存后缀，空则使用原后缀
        'replace' => false, // 存在同名是否覆盖
        'hash' => true, // 是否生成hash编码
        'callback' => false
    ), // 检测文件是否存在回调函数，如果存在返回文件信息数组
       // 图片上传相关配置（文件上传类配置）
       // '配置项'=>'配置值'
    'DEFAULT_CONTROLLER' => 'Public', // 后台默认访问的控制器
    'DEFAULT_ACTION' => 'login', // 后台默认访问的方法
    'URL_HTML_SUFFIX' => '', // 伪静态
    'PAGE_SETTING' => [
        'PAGE_SIZE' => 6,
        'ADMIN_GOODS_LIST' => 15
    ],
    
    // 订单状态【-1:,0 ，1，2，，3，4，5，6，7，8，9, 10：，11】
    'order' => array(
        'CancellationOfOrder' => '取消订单',
        'NotPaid' => '未支付',
        'YesPaid' => '已支付',
        'InDelivery' => '发货中',
        'AlreadyShipped' => '已发货',
        'ReceivedGoods' => '已收货',
        'ReturnAudit' => '退货审核中',
        'AuditFalse' => '审核失败',
        'AuditSuccess' => '审核成功',
        'Refund' => '退款中',
        'ReturnMonerySucess' => '退款成功',
        'ToBeShipped' => '代发货',
        'ReceiptOfGoods' => '待收货'
    ),
    /**
     * 代金卷类型
     */
    'COUPON_TYPE' => array(
        0 => '面额模板',
        1 => '按用户发放',
        2 => '注册发放',
        3 => '邀请发放',
        4 => '线下发放'
    ),
    /**
     * 退货
     */
    'returnGoods' => [ // 审核状态【0审核中1审核失败2审核通过3退货中4退款中5完成6.已撤销】
        '审核中',
        '审核失败',
        '审核通过',
        '退货中',
        '退款中',
        '完成',
        '已撤销'
    ],
    'approval' => [ // 是否可用【0：已申请 1：已通过 2：拒绝】
        0 => '已申请',
        1 => '<img width="20" height="20" src="/public/common/img/yes.png"/>',
        2 => '<img width="20" height="20" src="/public/common/img/cancel.png"/>'
    ],
    'estimate' => [ // 每月购买金额
        '2000-5000',
        '5000-10000',
        '10000以上'
    ],
    'goods_picture_number' => 8, // 上传商品图片数量
    'refund' => [
        '换货',
        '退货',
        '退款'
    ],
    'input_type' => [ // 商品属性
        0 => '手工录入',
        1 => '从列表中选择',
        2 => '多行文本框'
    ],
    'attr_index' => [
        '<img width="20" height="20" onclick="selectTool.recommend(this)" url="[XXXURL]" src="/public/common/img/cancel.png"/>',
        '<img width="20" height="20" onclick="selectTool.recommend(this)" url="[XXXURL]" src="/public/common/img/yes.png"/>'
    ],
    'image_type' => [
        '/public/common/img/cancel.png',
        '/public/common/img/yes.png'
    ],
    'is_receive' => [ // 是否收到货
        '',
        '未收到',
        '收到'
    ],
    'admin_log_type' => [ // 操作类型：0新增1修改2删除
        0 => '新增',
        1 => '修改',
        2 => '删除'
    ],
    'SHOW_PAGE_TRACE' => true,
    'ORDER_NUMBER' => 7,
    'PAGE_NUMBER' => 5,
    'admin_title' => 'shopsn电商系统',
    'upload_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/upload.php/Upload/index',
    'front_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/index.php/',
    'do_you_mail_it' => [ // 是否包邮
        1 => '自定义运费',
        2 => '卖家包邮'
    ],
    
    'charging_mode' => [ // 计费方式
        1 => '按件数',
        2 => '按重量',
        3 => '按体积'
    ],
    'pro_type' => [ //优惠类型配置
       'undefined',
       'gt',
       'gt'
    ],
    'specify_conditional_mail' => [ // 是否指定条件包邮
        '否',
        '是'
    ],
    
    'platform_pay' => [// 支付平台
        'pc',
        '移动设备（Phone）'
    ],
    
    'qr_image' => './Uploads/qrCode/',
    'water'    => './Public/Admin/img/logo/water.png',
    //input提示
    'placeholder' => [
        0  => '请输入商品名,不能超过60个字符',//商品名称
        1  => '请输入商品简介,不能超过100个字符',//商品简介
        2  => '当库存低于预警值时,商品列表页库存红色提醒，为0不预警',//库存预警
        3  => '商家编码，内部管理用',
    ],
    //管理员提示
    'title'  => [
        'recommend' => '优先在首页楼层对应分类展示',//推荐解释
        'hot' => '在首页热卖栏中显示',//热卖解释
    ],
);