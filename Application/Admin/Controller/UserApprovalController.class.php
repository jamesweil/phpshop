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
use Admin\Model\EnterpriseVipModel;
use Common\Tool\Tool;
use Admin\Model\UserModel;
use Common\Model\RegionModel;
use Admin\Model\ApprovalUserModel;

/**
 * 用户审批 
 */
class UserApprovalController extends AuthController
{
    /**
     * @desc 审批列表 
     * @author 王强
     */
    public function approval ()
    {
        $model = BaseModel::getInstance(EnterpriseVipModel::class);
        
        //设置搜索
        $search = $model->getSearch([
            EnterpriseVipModel::$companyName_d,
            EnterpriseVipModel::$applyName_d,
        ]);
        
        $this->assign('userModel', EnterpriseVipModel::class);
        
        $this->assign('search', $search);
        
        $this->display();
    }
    
    /**
     * ajax 获取列表 
     */
    public function ajaxGetApprovalUserList ()
    {
        $approvalModel = BaseModel::getInstance(EnterpriseVipModel::class);
        
        //设置要查询的字段
      
        Tool::connect('ArrayChildren');
        
        $where = $approvalModel->buildSearch($_POST, true);
        
        $approvalModel->setWhere($where);
        
        Tool::isSetDefaultValue($_POST, [//设置默认值
            'order_by' => EnterpriseVipModel::$id_d,
            'sort'     => BaseModel::DESC
        ]);
        
        $approvalModel->setOrder($_POST['order_by'].' ' .$_POST['sort']);
        
        //排序默认值
        $data = $approvalModel->getApprovalList(C('ORDER_NUMBER'));
        
        $userModel = BaseModel::getInstance(UserModel::class);
        
        Tool::connect('parseString');
        $data['data'] = $userModel->getDataByOtherModel($data['data'], EnterpriseVipModel::$userId_d, [
            UserModel::$id_d,
            UserModel::$userName_d
        ], UserModel::$id_d);
        
        $enterModel = BaseModel::getInstance(ApprovalUserModel::class);
        
       
       
        $data['data'] = $enterModel->getApprovalDataByUser( $data['data'], EnterpriseVipModel::$id_d);
        $this->assign('data', $data);
        $this->assign('approvalModel', EnterpriseVipModel::class);
        
        $this->assign('userModel', UserModel::class);
        $this->assign('approvalUserModel', ApprovalUserModel::class);
        
        $this->assign('approval', C('approval'));
        $this->display();
    }
    
    /**
     * 查看详情 
     */
    public function lookDetail ($id)
    {
        $this->errorNotice($id);
        
        $approvalModel = BaseModel::getInstance(EnterpriseVipModel::class);
        
        $data = $approvalModel->getAttribute([
            'field' => [EnterpriseVipModel::$createTime_d]
        ], true, 'find');
        $this->promptParse($data);
        //获取
        $userModel = BaseModel::getInstance(UserModel::class);
        
        $userName = $userModel->getUserNameById($data[EnterpriseVipModel::$userId_d], UserModel::$userName_d);
        
        $data[UserModel::$userName_d] = $userName;
        
        $_SESSION['approvalUserId'] = $data[EnterpriseVipModel::$userId_d];//保存用户编号
        
        $approvalUserModel = BaseModel::getInstance(ApprovalUserModel::class);
        
        $beOverdue = $approvalUserModel->getAttribute([
            'field' => ApprovalUserModel::$beOverdue_d,
            'where' => [ApprovalUserModel::$enterpriseId_d => $data[EnterpriseVipModel::$id_d], ApprovalUserModel::$isExpired_d => 1],
        ], false, 'find');
        
        $data[ApprovalUserModel::$beOverdue_d] = empty($beOverdue) ? null : $beOverdue[ApprovalUserModel::$beOverdue_d];
        
        //获取公司地址
        $data = BaseModel::getInstance(RegionModel::class)->getDefaultRegion($data, $approvalModel);
        
        $this->assign('approvalModel', EnterpriseVipModel::class);
        
        $this->assign('userModel', UserModel::class);
        $this->assign('data', $data);
        
        $this->assign('statusApproval', $approvalModel->getStatus());
        
        $this->assign('estimate', C('estimate'));
        $this->assign('approvalUser', ApprovalUserModel::class);
        $this->display();
    }
    
    /**
     * 审核 
     */
    public function approvalUser()
    {
        $validate = [
            'enterprise_id',
            'status',
            'be_overdue',
        ];
        
        $must = [
            'enterprise_id',
            'status',
        ];
        Tool::checkPost($_POST, ['is_numeric' => $validate, 'be_overdue'], true, $must) ? : $this->ajaxReturnData(null, 0, '操作失败');
        
        $model = BaseModel::getInstance(EnterpriseVipModel::class);
        
        $status = $model->saveData($_POST);
        
        $userApprovalModel = BaseModel::getInstance(ApprovalUserModel::class);
        
        //修改用户状态
        $userModel = BaseModel::getInstance(UserModel::class);
        
        $userStatus = $userModel->editStatus ($_SESSION['approvalUserId'], $_POST[EnterpriseVipModel::$status_d]);
        
        $this->promptPjax($userStatus, '更新失败');
        
        $status = $userApprovalModel->addApproval($_POST, EnterpriseVipModel::$status_d);
        
        $this->promptPjax($status !== false, '更新失败');
        
        $this->ajaxReturnData(['url' =>U('approval')]);
    }
}