<?php
define('__STATIC_MOUDLE__', 'Upload');
return array(
    'LOAD_EXT_CONFIG'    => COMMON_PATH.'Conf/tmpl.php', // 加载配置文件
    'IMAGE_UPLOAD_SERVER' => 'http://fxcs.03.idchome.net/index.php/Home/FileUpload/receiveFile',
    'IMG_DOMAIN'          => 'http://fxcs.03.idchome.net',
    /* 图片上传相关配置 */
    'GOODS_UPLOAD' => array(
        'LOAD_EXT_CONFIG'    => COMMON_PATH.'Conf/tmpl.php', // 加载配置文件
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/brand/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //图片上传相关配置（文件上传类配置）
    
    'brand_banner_config' => [// 品牌banner 宽高设置
        'brand_image_min_width',
        'brand_banner_width',
        'brand_image_min_height',
        'brand_banner_height',
    ],
    
    'brand_logo_config' => [ // 品牌logo 宽高设置
        'brand_image_min_width',
        'brand_logo_with',
        'brand_image_min_height',
        'brand_logo_height',
    ],
    'image_config' => [ // 商品图片宽高度
        'goods_image_min_width',
        'image_width',
        'goods_image_min_height',
        'image_height',
    ],
    
    //商品分类图片最小宽度
    'class_image_conf' => [
        'class_image_min_width',
        'goods_class_width',
        'class_image_min_height',
        'goods_class_height',
    ],
    //网站logo
    'intnet_logo_config' =>[
        'logo_min_width',
        'max_logo_width',
        'min_logo_height',
        'max_logo_height'
    ]
);