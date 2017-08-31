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
 
namespace Home\Model;

use Think\Model;

/**
 * 如果是第三方登陆的话
 * 1.先获取授权,保存到数据库
 * 2.创建用户,绑定授权到 db_user 表中
 */
class UserAuthsModel extends Model
{

    /**
     * 获取用户基本信息
     * @param  string  $identifier    用户唯一标识:可以是手机,邮箱,第三方唯一标识...
     * @param  integer $identity_type 登录类型: 1.手机登录,2.邮箱登录,3.支付宝登录,4.qq登录5.微信登录,6.微博登录
     * @param  boolean $valid         检测过期:手机登录,邮箱登录无效  (对用户账户状态无效,是否冻结...),返回值多一个valid字段
     * @return array
     */
    public function findInfo($identifier, $identity_type = 1, $valid = false)
    {
        $field = 'a.user_id, u.mobile, u.user_name, u.nick_name, u.status,a.id as auth_id, a.credential, a.expires_in, a.update_at, a.local';
        $where = ['a.identity_type' => $identity_type, 'a.identifier' => $identifier];
        $info  = $this->alias('a')->join('__USER__ as u On a.user_id=u.id')->field($field)->where($where)->find();

        // 检测有效性,针对本地登录账户有效
        if ($valid && is_array($info) && !empty($info) && !$info['local'] && $info['expires_in'] != -1) {
            $expires = ($info['expires_in'] + $info['update_at']) < time();
            $info['valid'] = !$expires;
        }
        return $info;
    }

    /**
     * 获取授权列表
     * @param  integer $user_id 用户id
     * @return array
     */
    public function authList($user_id)
    {
        $field = 'identity_type, identifier, credential, expires_in, local';
        $list  = $this->field($field)->where(['user_id' => $user_id])->select();
        if (is_array($list)) {
            return $list;
        }
        return [];
    }

    /**
     * 保存用户信息
     * 注意保存检测是否已经绑定了信息
     * @param  array   $param   数据项:id,user_id,identity_type,identifier,credential,expires_in,update_at,create_at
     * @return boolean           
     */
    public function saveInfo($param)
    {
        $time                  = time();
        $data['user_id']       = $param['user_id'];
        $data['identity_type'] = $param['identity_type'];
        $data['identifier']    = $param['identifier'];
        $data['credential']    = $param['credential'];
        $data['expires_in']    = $param['expires_in'];
        $data['local']         = $param['local'];
        $data['update_at']     = $time;
        if ($param['id']) {
            $data['id'] = $param['id'];
            return $this->save($data);
        }
        $data['create_at'] = $time;
        return $this->add($data); 
    }
}