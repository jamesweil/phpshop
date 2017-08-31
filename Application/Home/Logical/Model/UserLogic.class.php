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
namespace Home\Logical\Model;

use Common\Model\BaseModel;
use Home\Model\UserModel;
use Home\Model\BalanceModel;

/**
 * 用户逻辑处理
 */
class UserLogic
{
    private $userModel;
    
    private $id;
    
    private $post = [];
    
    private $error = '';
    
    private $getData;
    
    /**
     * @param int $id 用户编号
     * @param array $post post数据
     */
    public function __construct($id, $post)
    {
        $this->userModel = UserModel::getInitnation();
        
        $this->id = (int)$id;
        
        $this->post = (array)$post;
    }
    
    /**
     * 获取账户余额信息
     */
    public function getUsersBlanace ()
    {
        $data = $this->userModel->field(UserModel::$password_d)->find($this->id);
        
        $balance = BaseModel::getInstance(BalanceModel::class)->getBalanceMoney();
        
        $data['balance'] = $balance;
        
        $this->getData =  $this->error($data);
        
        return $this->getData;
    }
    
    /**
     * 验证密码
     * @param unknown $userData
     * @return boolean|unknown
     */
    public function vaildatePassWord()
    {
        $userData = $this->getData;
       
        return $this->error($userData[UserModel::$password_d] === md5($this->post[UserModel::$password_d]), '密码不符');
    }
    
    /**
     * 验证余额
     * @param unknown $userData
     * @return boolean|unknown
     */
    public function validateBalance ()
    {
        $userData = $this->getData;
        
        $price = (float)$this->post['price_sum'];
        
        if (!$this->error($price, '余额不足') || $price <=0) {
            return false;
        }
        
        return $this->error((float)$userData['balance'] > (float)$price, '余额不足');
    }
    
    /**
     * @return the $error
     */
    public function getError()
    {
        return $this->error;
    }

    public function error ($userData, $errorMsg = '不存在用户数据')
    {
        if (empty($userData)) {
            $this->error = $errorMsg;
            return false;
        }
        return $userData;
    }
    
    /**
     * 支付处理
     */
    public function balancePayParse ()
    {
        $this->userModel->startTrans();
        //添加余额消费记录
        $monery = $this->getData['balance'] - $this->post['price_sum']; 
        
        $status = BaseModel::getInstance(BalanceModel::class)->addBalanceLogs($monery);
        
        return $status;
    }
    
    protected function parseTranceMsg ($status, $msg = '余额处理失败')
    {
        if ($status === false) {
            $this->userModel->rollback();
            $this->error = $msg;
            return false;
        }
        return true;
    }
}