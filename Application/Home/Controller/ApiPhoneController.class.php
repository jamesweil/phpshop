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
use Common\TraitClass\SmsVerification;
use Common\Tool\Tool;
use Common\TraitClass\NoticeTrait;

class ApiPhoneController extends Controller
{
    use NoticeTrait;
    use SmsVerification;
    
    // 登录
    public function login($tel)
    {
        $this->sms();
    }
    // 重置密码
    public function reset_pwd($tel)
    {
        $this->sms();
    }
    // 注册
    public function reg($tel)
    {
        $this->sms();
    }
    // 绑定手机
    public function bound_phone($tel)
    {
        $this->sms();
    }
    
    /**
     * 发送验证码
     */
    private function sms()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'tel'
            )
        ), true, array(
            'tel'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $smsConfig = $this->getConfig();
        
        $res = $this->SmsVerification($smsConfig, $_POST['tel']);
        
        $this->updateClient($res, '操作');
    }
}