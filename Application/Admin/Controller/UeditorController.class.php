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


namespace Admin\Controller;

use Common\Controller\AuthController;
use Think\Upload1;
use Common\Tool\Event;
use Common\Tool\Extend\Session;

/**
 * Class UeditorController
 * @package Admin\Controller
 */
class UeditorController extends AuthController
{
    private $sub_name = array('date', 'Y/m-d');
    private $savePath = 'temp/';

    public function __construct()
    {
        if (!empty($_GET['sId'])) {
            $sId = base64_decode($_GET['sId']);
            Event::insetListen('sId', function (&$param)use($sId){
                $param = $sId;
            });
            (new Session())->setSession('*');
        }
        
        parent::__construct();

        date_default_timezone_set("Asia/Shanghai");
        
        $this->savePath = I('GET.savepath','temp').'/';
        
    }
    
    
    /**
     * @function imageUp
     */
    public function imageUp()
    {
        // 上传图片框中的描述表单名称，
        $title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
        $path = htmlspecialchars($_POST['dir'], ENT_QUOTES);        

        $config = array(
            "savePath" => $this->savePath,
            "maxSize"  => 20000000, // 单位B
            "exts"     => explode(",", 'gif,png,jpg,jpeg,bmp'),
            "subName"  => $this->sub_name,
        );

        $upload = new Upload1($config);
        $info = $upload->upload();

        if ($info) {
            $state = "SUCCESS";         
        } else {
            $state = "ERROR" . $upload->getError();
        }
        if(!isset($info['upfile'])){
        	$info['upfile'] = $info['Filedata'];
        }
        $return_data['url'] = $info['upfile']['urlpath'];
        $return_data['title'] = $title;
        $return_data['original'] = $info['upfile']['name'];
        $return_data['state'] = $state;
        $this->ajaxReturn($return_data,'json');
    }

}