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

namespace Home\Controller;

use Think\Controller;
use Common\Tool\Tool;
use Home\Model\UserHeaderModel;

class FileUploadController extends Controller
{
    public function receiveFile()
    {
        Tool::checkPost($_FILES)? true : $this->ajaxReturnData(null, '400', '参数错误');
        Tool::checkPost($_POST, array('is_numeric' => array('user_id')), true, array('user_id'))? true : $this->ajaxReturnData(null, '400', '参数错误');
       
        Tool::connect('File');
        $_FILES = Tool::parseFile($_FILES);
        
        Tool::connect('Mosaic');
        
        $fileObj = UserHeaderModel::getInitation();
        $file    = $fileObj->UploadFile(C('USER_UPLOAD'));
        
        $insert  = $fileObj->updateOrAdd($file, $_POST['user_id']);
        $status = empty($insert) ? 0 : 1;
       
        $mssage = empty($insert) ? '失败':'成功';
       
        $this->ajaxReturnData($insert, $status, $mssage);
    }
    
    /**
     * ajax 返回数据
     */
    protected function ajaxReturnData($data, $status= 1, $message = '操作成功')
    {
        $this->ajaxReturn(array(
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ));
        die();
    }
}