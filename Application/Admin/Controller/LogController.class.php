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
use Common\Model\BaseModel;
use Admin\Model\LogModel;
use Admin\Model\AdminModel;
use Common\Tool\Tool;
use Admin\Model\LogContentModel;

/**
 * 日志控制器 
 */
class LogController extends AuthController
{
    /**
     * 日志列表 
     */
    public function logList ()
    {
        $model = BaseModel::getInstance(LogModel::class);
        
        $this->assign('logModel', LogModel::class);        
       
        $tabComment = $model->getAllTableNotes();
        
        $this->logType();
        
        $this->assign('tableNote', $tabComment);
        
        $this->display();
    }
    
    /**
     * ajax 获取 日志列表 
     */
    public function ajaxGetListLog ()
    {
        $logModel = BaseModel::getInstance(LogModel::class);
        
        Tool::connect('ArrayChildren');
        //---------------------------------------------------------------------- 组织搜索条件
        $userModel = BaseModel::getInstance(AdminModel::class);
        
        $userModel->setAdminId(LogModel::$adminId_d);
        
        $logModel->setSearchCreateTimeKey(LogModel::$createTime_d);
        
        
        $userWhere = $userModel->getAdminUserWhere ($_POST);
       
        
        $where = $logModel->buildSearch($_POST, false, [LogModel::$adminId_d]);
        
        $where = array_merge($userWhere, $where);
        //-------------------------------------------------------------------------
        //获取注释
        $notes = $logModel->getComment();
        
        //分页获取数据
        
        $data     = $logModel->getLogByPage($where, 15);
        
        //获取管理员数据
        
        $model = BaseModel::getInstance(AdminModel::class);
        
        Tool::connect('parseString');
        
        $data['data'] = $model->getAdminUserData ($data['data'], LogModel::$adminId_d);
        
        $this->logType();
        
        $this->assign('data', $data);
        
        $this->assign('logModel', LogModel::class);
        
        $this->assign('adminModel', AdminModel::class);
        
        $this->assign('notes', $notes);
        
        $this->display();
    }
    
    /**
     * 查看详情 
     */
    public function lookDetail($id)
    {
        $this->errorNotice($id);
        
        $model = BaseModel::getInstance(LogContentModel::class);
        
        //获取注释
        $notes = $model->getComment([LogContentModel::$logId_d, LogContentModel::$createTime_d]);
        
        $data   = $model->getNoteLog($id);
        
        $this->assign('data', $data);
        
        $this->assign('notes', $notes);
        
        $this->assign('logContentModel', LogContentModel::class);
        
        $this->display();
        
    }
    
    /**
     * 获取日志类型 
     */
    private function logType ()
    {
        $logType =  C('admin_log_type');
        $this->assign('logType', $logType);
    }
    
    /**
     * 删除日志 【父级】
     * @param int $id 父级日志编号
     * @return \JsonSerializable
     */
    public function deleteLogByParent($id)
    {
        $id = (int)$id;
        $this->promptPjax($id, '参数错误');
        
        $logModel = BaseModel::getInstance(LogModel::class);
        
        $status   = $logModel->deleteId($id);
        
        $this->promptPjax($status, '删除失败');
        
        $logContentModel = BaseModel::getInstance(LogContentModel::class);
        
        $status = $logContentModel->deleteByLogId($id);
        
        $this->promptPjax($status, '删除失败');
        
        $this->ajaxReturnData([
            'url' => U('logList')
        ]);
    }
    
    /**
     * 根据编号删除从表 日志数据 
     * @param int $id 父级日志编号
     * @return \JsonSerializable
     */
    public function deleteLogById($id)
    {
        $id = (int)$id;
        $this->promptPjax($id, '参数错误');
    
        $logContentModel = BaseModel::getInstance(LogContentModel::class);
    
        $status = $logContentModel->deleteById($id);
    
        $this->updateClient($status, '删除');
    }    
}