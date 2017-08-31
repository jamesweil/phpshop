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
use Common\TraitClass\NoticeTrait;
use Common\Model\BaseModel;
use Common\Model\UserAddressModel;
use Common\TraitClass\InternetTopTrait;

/**
 * 用户收货地址  
 */
class UserAddressController extends Controller
{
    use NoticeTrait;
    use InternetTopTrait;
    
    private static $validate = [
            'mobile',
            'prov',
            'city',
            'dist'
        ];

    public function __construct()
    {
        parent::__construct();
        
        $this->isLogin(true);
    }
    
    /**
     *  添加收货地址
     */
    public function addReceiveAddress()
    {
        unset($_POST['id']);
        unset($_POST['prov_name']);
        unset($_POST['city_name']);
        unset($_POST['dist_name']);

        $must = array_merge(self::$validate, ['realname','address']);

        $model = BaseModel::getInstance(UserAddressModel::class);
        
        $isPuss = self::flag($must);
        
        $this->promptPjax($isPuss, '数据验证未通过');
        
        $data = $model->getAttribute([
            'field' => [UserAddressModel::$id_d],
            'where' => [UserAddressModel::$userId_d => $_SESSION['user_id'], UserAddressModel::$address_d => $_POST['address']]
        ]);
        
        $this->alreadyInData($data);
        
        $status = $model->addUserAddress($_POST);
        
        
        $this->updateClient($status, '添加');
        
    }
    
    /**
     * 修改数据 【地址】
     */
    public function editAddress()
    {
        self::$validate[] = 'id';
        unset($_POST['prov_name']);
        unset($_POST['city_name']);
        unset($_POST['dist_name']);

        $must = array_merge(self::$validate, [ 'realname','address', 'id']);
        
        $model = BaseModel::getInstance(UserAddressModel::class);
        
        $isPuss = self::flag($must);
        
        $this->promptPjax($isPuss, '数据验证未通过');
        
        $status = $model->save($_POST);
        $this->updateClient($status, '修改');
        
    }

    /**
     *  辅助验证方法
     */
    private static function flag($must)
    {
        $notCheck = [
            'is_numeric' => self::$validate, 
            'zipcode',
            'telphone',
            'email',
            'alias'
        ];
        $validate = Tool::checkPost($_POST, $notCheck, true, $must);
      
        if ($validate === false) {
            return false;
        }
        
        //匹配规则验证
        $rule = [
            UserAddressModel::$realname_d => $_POST[UserAddressModel::$realname_d] ,
            UserAddressModel::$mobile_d   => $_POST[UserAddressModel::$mobile_d]
        ];
        
        //创建正则表达式
        $regex = [
            UserAddressModel::$realname_d => '/[\x{4e00}-\x{9fa5}]+/u',
        ];
        
        if (!empty($_POST[UserAddressModel::$email_d])) {
            $regex[UserAddressModel::$email_d] = '/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i';
             
            $rule[UserAddressModel::$email_d]  = $_POST[UserAddressModel::$email_d];
        }
        
        if (!empty($_POST[UserAddressModel::$telphone_d])) {
            $regex[UserAddressModel::$telphone_d] = '/^[0-9]{3,4}-?[0-9]{7,8}$/';
            $rule[UserAddressModel::$telphone_d] = $_POST[UserAddressModel::$telphone_d];
        }

        //验证数据
        $isPuss = Tool::connect('ParttenTool', $regex)->checkPartten($rule);
        
        return $isPuss;
    }

    /**
     * 获取区域,不传入参数获取顶级区域
     * @param  integer $region 父区域ID
     * @return array
     */
    public function region()
    {
        $rid = I('region', -1, 'intval');
        if (empty($rid) || $rid == -1) {
            $where['parentid'] = '0';
        } else {
            $where['parentid'] = $rid;
        }
        $data = M('region')->field('id, parentid, name')->where($where)->select();
        $this->ajaxReturn($data);
    }

    /**
     * 获取用户地址
     */
    function find()
    {
        $addr_id = I('addr_id', -1);
        if ($addr_id == -1 || empty($addr_id)) {
            $this->ajaxReturn('参数错误');
        }

        $addr = D('UserAddress')->getAddrById($addr_id);
        $this->ajaxReturn($addr);
    }
 
    /**
     * 增加新地址
     */
    public function add()
    {
        $model           = D('UserAddress');
        $data            = I('POST.');
        $data['user_id'] = $_SESSION['user_id'];
        $ret             = $model->store($data);


        $ret = D('UserAddress')->getAddrById($addr_id);

        $this->ajaxReturn($ret);
    }


    /**
     * 地址编辑
     */
    public  function edit()
    {
        $model           = D('UserAddress');
        $data            = I('POST.');
        $data['user_id'] = $_SESSION['user_id'];
        $ret = $model->edite($data);
        $this->ajaxReturn(intval($ret));
    }
}