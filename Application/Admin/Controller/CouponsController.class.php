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
use Admin\Model\CouponModel;
use Common\Tool\Tool;
use Admin\Model\UserModel;
use Admin\Model\UserLevelModel;
use Think\AjaxPage;
use Admin\Model\CouponListModel;
use Admin\Model\OrderModel;

/**
 * 优惠劵列表
 */
class CouponsController extends AuthController
{

    /**
     * 优惠劵列表
     */
    public function couponsList()
    {
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        $data = $couponModel->getDataByPage(array(
            'field' => array(
                $couponModel::$updateTime_d,
                $couponModel::$addTime_d
            ),
            'order' => $couponModel::$updateTime_d . BaseModel::DESC . ',' . $couponModel::$addTime_d . BaseModel::DESC
        ), 20, true);
        
        $this->data = $data;
        
        $this->couponModel = $couponModel;
        
        $this->couponType = C('COUPON_TYPE');
        
        $this->display();
    }

    /**
     * 添加优惠券
     */
    public function addConponHtml()
    {
        BaseModel::getInstance(CouponModel::class);
        
        $this->conponModel = CouponModel::class;
        
        $this->display();
    }

    /**
     * 添加代金卷 到数据库
     */
    public function addCouponData()
    {
        $this->flag();
        
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        $isExits = $couponModel->getAttribute(array(
            'field' => array(
                $couponModel::$id_d
            ),
            'where' => array(
                $couponModel::$name_d => $_POST['name']
            )
        ));
        
        $this->alreadyInDataPjax($isExits);
        
        $status = $couponModel->addData($_POST);
        
        $this->updateClient($status, '操作');
    }

    /**
     * 发放优惠券 ['按用户发放']
     */
    public function sendCoupon()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id',
                'type'
            )
        ), true, array(
            'id',
            'type'
        )) ? true : $this->error('参数错误');
        $userLevelModel = BaseModel::getInstance(UserLevelModel::class);
        
        $data = $userLevelModel->getAttribute(array(
            'field' => array(
                $userLevelModel::$id_d,
                $userLevelModel::$levelName_d
            ),
            'order' => $userLevelModel::$id_d . BaseModel::DESC
        ));
        
        $userModel = BaseModel::getInstance(UserModel::class);
        
        Tool::isSetDefaultValue($_POST, array(
            $userModel::$userName_d => '',
            $userModel::$mobile_d => '',
            $userModel::$email_d => ''
        ));
        
        $userModel = BaseModel::getInstance(UserModel::class);
        
        BaseModel::getInstance(CouponListModel::class);
        
        $this->assign('data', $data);
        $this->assign('user', UserModel::class);
        $this->assign('userLevel', UserLevelModel::class);
        $this->assign('couponModel', CouponListModel::class);
        $this->assign('levelId', 0);
        $this->display();
    }

    /**
     * 发放代金卷【按用户发放】按照用户等级
     */
    public function sendCouponByUserLevel()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'c_id'
            )
        ), true, array(
            'c_id',
            'id',
            'type'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $userModel = BaseModel::getInstance(UserModel::class);
        
        $userData = $userModel->getAttribute(array(
            'field' => array(
                $userModel::$id_d
            ),
            'where' => array(
                $userModel::$levelId_d => $_POST['id'],
                $userModel::$status_d => 1
            )
        ));
        $this->promptPjax($userData, '未找到这样的会员');
        
        self::isSend(count($userData), $_POST['c_id']);
        
        // 传递代金券列表
        
        $couponListModel = BaseModel::getInstance(CouponListModel::class);
        unset($_POST['id']);
        
        $status = $couponListModel->addCouponList($_POST, $userData);
        
        $this->updateClient($status, '操作');
    }

    /**
     * 筛选发放代金卷【按用户发放】
     */
    public function sendCouponUser()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'c_id'
            )
        ), true, array(
            'c_id',
            'id',
            'type'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        // 是否还能发放
        
        $isSendCoupon = $couponModel->isSendCoupon(count($_POST['id']), $_POST['c_id']);
        
        $this->promptPjax($isSendCoupon, $couponModel->getCouponError());
        
        $couponList = BaseModel::getInstance(CouponListModel::class);
        
        $status = $couponList->addAll($_POST);
        
        $this->updateClient($status, '操作，');
    }

    /**
     * 查看发放代金卷的会员
     */
    public function couponListByUser()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id',
                'type'
            )
        ), true, array(
            'id',
            'type'
        )) ? true : $this->error('参数错误');
        
        // 代金券名称
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        $couponData = $couponModel->getAttribute([
            'field' => [
                $couponModel::$name_d
            ],
            'where' => [
                $couponModel::$id_d => $_GET['id']
            ]
        ], false, 'find');
        
        $this->prompt($couponData, '未知代金卷');
        
        $couponListModel = BaseModel::getInstance(CouponListModel::class);
        
        $data = $couponListModel->getDataByPage(array(
            'field' => array(
                $couponListModel::$id_d,
                $couponListModel::$cId_d,
                $couponListModel::$userId_d,
                $couponListModel::$orderId_d,
                $couponListModel::$sendTime_d,
                $couponListModel::$code_d,
                $couponListModel::$type_d
            ),
            'where' => array(
                $couponListModel::$cId_d => $_GET['id']
            )
        ));
        $this->prompt($data['data']);
        
        // 传递用户表
        $userModel = BaseModel::getInstance(UserModel::class);
        
        Tool::connect('parseString');
        
        $data['data'] = $userModel->getDataByOtherModel($data['data'], $couponListModel::$userId_d, array(
            $userModel::$id_d . BaseModel::DBAS . $couponListModel::$userId_d,
            $userModel::$userName_d
        ), $userModel::$id_d);
        
        // 传递订单表
        $orderModel = BaseModel::getInstance(OrderModel::class);
        
        $data['data'] = $orderModel->getDataByOtherModel($data['data'], $couponListModel::$orderId_d, array(
            $orderModel::$id_d,
            $orderModel::$orderSn_id_d
        ), $orderModel::$id_d);
        
        $this->data = $data;
        $this->couponData = $couponData;
        $this->coupon = C('COUPON_TYPE');
        $this->userModel = UserModel::class;
        $this->couponModel = CouponModel::class;
        $this->orderModel = OrderModel::class;
        $this->couponList = CouponListModel::class;
        $this->display();
    }

    /**
     * 删除代金卷
     */
    public function deleteCoupon()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $status = BaseModel::getInstance(CouponModel::class)->delete(array(
            'where' => array(
                CouponModel::$id_d => $_POST['id']
            )
        ));
        
        $this->updateClient($status, '操作');
    }

    /**
     * ajax 获取用户
     */
    public function ajaxGetUser()
    {
        $userModel = BaseModel::getInstance(UserModel::class);
        
        // 组装筛选控件
        Tool::connect('ArrayChildren');
        $where = $userModel->buildSearch($_POST);
        
        $data = $userModel->getDataByPage(array(
            'field' => array(
                $userModel::$id_d,
                $userModel::$userName_d,
                $userModel::$mobile_d,
                $userModel::$email_d,
                $userModel::$levelId_d
            ),
            'where' => $where,
            'order' => $userModel::$createTime_d . BaseModel::DESC . ',' . $userModel::$updateTime_d . BaseModel::DESC
        ), 15, false, AjaxPage::class);
        
        // 传递等级表
        $userLevel = BaseModel::getInstance(UserLevelModel::class);
        
        Tool::connect('parseString');
        $data['data'] = $userLevel->getLevelByUser($data['data'], $userModel::$levelId_d);
        
        $this->assign('userLevel', UserLevelModel::class);
        
        $this->assign('data', $data);
        
        $this->assign('userModel', UserModel::class);
        
        $this->display();
    }

    /**
     * 发放优惠劵【 '线下发放'】
     */
    public function makeCoupon()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id',
                'type'
            )
        ), true, array(
            'id',
            'type'
        )) ? true : $this->error('参数错误');
        
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        $data = $couponModel->getAttribute(array(
            'field' => array(
                $couponModel::$id_d,
                $couponModel::$name_d,
                $couponModel::$type_d,
                $couponModel::$money_d
            ),
            'where' => array(
                $couponModel::$id_d => $_GET['id']
            )
        ), false, 'find');
        $this->promptPjax($data, '未发现此代金劵');
        $this->coupon = $data;
        $this->couponModel = $couponModel;
        $this->display();
    }

    /**
     * 删除 发放的代金卷
     */
    public function couponListDel()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->error('参数错误');
        
        $status = BaseModel::getInstance(CouponListModel::class)->delete(array(
            'where' => array(
                CouponListModel::$id_d => $_GET['id']
            )
        ));
        empty($status) ? $this->error('参数错误') : $this->success('操作成功');
    }

    /**
     * 发放优惠劵【 '线下发放'】
     */
    public function makeCouponUser()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'num',
                'id',
                'type'
            )
        ), true, array(
            'num',
            'id',
            'type'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        // //检测代金卷
        self::isSend($_POST['num'], $_POST['id']);
        
        $couponListModel = BaseModel::getInstance(CouponListModel::class);
        
        Tool::connect('Token');
        
        $status = $couponListModel->addMakeCoupon($_POST);
        
        $this->updateClient($status, '操作方法');
    }

    /**
     * 代金卷编辑 页
     */
    public function editCouponHTML()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->error('参数错误');
        
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        $data = $couponModel->getAttribute(array(
            'field' => array(
                $couponModel::$addTime_d,
                $couponModel::$updateTime_d
            ),
            'where' => array(
                $couponModel::$id_d => $_GET['id']
            )
        ), true, 'find');
        $this->prompt($data);
        
        $this->coupon = $data;
        
        $this->couponModel = CouponModel::class;
        
        $this->display();
    }

    /**
     * 保存编辑
     */
    public function saveEdit()
    {
        $this->flag(true);
        
        $status = BaseModel::getInstance(CouponModel::class)->addData($_POST, true);
        
        $this->updateClient($status, '操作');
    }

    /**
     * 辅助方法
     */
    private final function isSend($num, $couponId)
    {
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        // 检测代金券是否存在
        
        $coupon = $couponModel->getAttribute(array(
            'field' => array(
                CouponModel::$id_d
            ),
            'where' => array(
                CouponModel::$id_d => $couponId
            )
        ));
        
        $this->promptPjax($coupon);
        
        // 是否还能发放
        
        $isSendCoupon = $couponModel->isSendCoupon(count($num), $couponId);
        
        $this->promptPjax($isSendCoupon, $couponModel->getCouponError());
        
        return true;
    }

    /**
     * 辅助方法
     */
    private function flag($flag = FALSE)
    {
        $arr = array();
        
        if (! $flag) {
            $arr = array(
                'type',
                'money',
                'condition',
                'createnum'
            );
        } else {
            $arr = array(
                'money',
                'condition',
                'createnum',
                'id'
            );
        }
        return Tool::checkPost($_POST, $arr, true, $arr) ? true : $this->ajaxReturnData(null, 0, '参数错误');
    }
}