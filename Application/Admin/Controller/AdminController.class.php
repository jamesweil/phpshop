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
use Think\Auth;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
use Common\Tool\Tool;
use Common\Model\BaseModel;
use Admin\Model\AuthGroupAccessModel;
use Admin\Model\AdminModel;

// 后台管理员
class AdminController extends AuthController
{
    // 用户列表
    public function admin_list()
    {
        $m = M('Admin');
        $nowPage = isset($_GET['p']) ? $_GET['p'] : 1;
        
        // page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
        $data = $m->order('id DESC')
            ->page($nowPage . ',' . PAGE_SIZE)
            ->select();
        $auth = new Auth();
        foreach ($data as $k => $v) {
            $group = $auth->getGroups($v['id']);
            $data[$k]['group'] = $group[0]['title'];
        }
        // 分页
        $count = $m->where($where)->count(id); // 查询满足要求的总记录数
        $page = new \Think\Page($count, PAGE_SIZE); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('data', $data);
        $this->display();
    }
    
    // 检查账号是否已注册
    public function check_account()
    {
        $m = M('admin');
        $where['account'] = I('account'); // 账号
        $data = $m->field('id')
            ->where($where)
            ->find();
        if (empty($data)) {
            $this->ajaxReturn(0); // 不存在
        } else {
            $this->ajaxReturn(1); // 存在
        }
    }
    
    // 添加用户
    public function admin_add()
    {
        $m = M('auth_group');
        $data = $m->field('id,title')->select();
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 添加用户
     */
    public function addAdminUserDataBase()
    {
        $isNumeric = ['group_id'];
        
        $mustExits = ['group_id', 'password', 'account'];
       
        Tool::checkPost($_POST, ['is_numeric' => $isNumeric], true, $mustExits) ? : $this->error('添加失败');
        
        $adminModel = BaseModel::getInstance(AdminModel::class);
       
        $insertId = $adminModel->addAdminUser($_POST);
        
        $this->promptPjax($insertId, $adminModel->getError());
        
        $authGroupAccess = BaseModel::getInstance(AuthGroupAccessModel::class);
        
        // 添加用户到db_auth_access
        $authGroupAccess->setReceivePost($_POST);
        
        $status = $authGroupAccess->addGroupAccess ($insertId);
        
        $this->updateClient($status, '添加');
        
    }

    /**
     * 保存编辑
     */
    public function saveAudit()
    {
        // 修改所属组
        $access = M('auth_group_access');
        if (empty($_POST['group_id'])) {
            $this->error('请选择用户组');
        }
        $where2['uid'] = $_POST['id'];
        $result = $access->where($where2)->find();
        
        if (empty($result)) {
            $map['uid'] = $_POST['id'];
            $map['group_id'] = $_POST['group_id'];
            $access->add($map);
        } else {
            $save['group_id'] = $_POST['group_id'];
            
            $access->where('uid=' . $_POST['id'])->save($save);
        }
        
        $data['id'] = $_POST['id'];
        if (! empty($_POST['password'])) {
            $data['password'] = md5($_POST['password']);
        }
        if ($_POST['status'] >= 0) {
            $data['status'] = $_POST['status'];
        }
        
        $data['group_id'] = $_POST['group_id'];
        
        $m = M('admin');
        $result = $m->where('id=' . $data['id'])->save($data);
        if ($result === false) {
            $this->error('修改失败');
        } else {
            $this->success('修改成功');
        }
    }
    // 编辑
    public function admin_edit()
    {
        $m = M('admin');
        $result = $m->where('id=' . I('id'))->find();
        // 获取当前所属组
        $auth = new Auth();
        $group = $auth->getGroups($result['id']);
        $result['title'] = $group[0]['title'];
        $result['group_id'] = $group[0]['group_id'];
        $this->assign('vo', $result);
        // 获取所有组
        $m = M('auth_group');
        $group = $m->order('id DESC')->select();
        $this->assign('group', $group);
        $this->display();
    }

    /**
     * 保存编辑
     */
    public function admin_save()
    {
        $validate = [
            'id',
            'group_id',
            'status'
        ];
        
        Tool::checkPost($_POST, [
            'is_numeric' => $validate
        ], true, $validate) ?: $this->error('修改失败');
        
        // 修改所属组
        $access = BaseModel::getInstance(AuthGroupAccessModel::class);
        $access->setIsOpenTranstion(true);
        $status = $access->save([
            AuthGroupAccessModel::$groupId_d => $_POST['group_id']
        ], [
            'where' => [
                AuthGroupAccessModel::$uid_d => $_POST['id']
            ]
        ]);
        
        $this->promptParse($status !== false, '修改失败');
        
        $m = BaseModel::getInstance(AdminModel::class);
        $status = $m->saveEdit($_POST);
        $this->promptParse($status !== false, '修改失败');
        
        $this->success('修改成功');
    }
    
    // 删除用户
    public function admin_del()
    {
        $id = $_POST['id']; // 用户ID
        if ($id == 1) {
            $this->ajaxReturn(0); // 不允许删除超级管理员
        }
        $m = M('auth_group_access');
        $m->where('uid=' . $id)->delete(); // 删除权限表里面给的权限
        
        $m = M('admin');
        $result = $m->where('id=' . $id)->delete();
        if ($result) {
            $this->ajaxReturn(1); // 成功
        } else {
            $this->ajaxReturn(0); // 删除失败
        }
    }
    
    // 角色-组
    public function auth_group()
    {
        $m = M('auth_group');
        $data = $m->order('id DESC')->select();
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 保存
     */
    public function addAudit()
    {
        Tool::checkPost($_POST, (array) null, false, array(
            'title',
            'rules'
        )) ? true : $this->error('参数错误');
        $_POST['rules'] = implode(',', $_POST['rules']);
        $status = AuthGroupModel::getInitnation()->add($_POST);
        $status ? $this->success('添加成功') : $this->error('添加失败');
    }
    // 添加组
    public function group_add()
    {
        $data = $this->getAduit();
        $this->assign('data', $data); // 顶级
        $this->display();
    }

    public function saveEdit()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id',
            'title',
            'rules'
        )) ? true : $this->error('参数错误');
        $_POST['rules'] = implode(',', $_POST['rules']);
        $status = AuthGroupModel::getInitnation()->save($_POST);
        $status !== false ? $this->success('修改成功') : $this->error('修改失败');
    }
    // 编辑组
    public function group_edit()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->error('参数错误');
        
        $reuslt = AuthGroupModel::getInitnation()->getAuthGroupById('id,title,rules', array(
            'id' => $_GET['id']
        ), 'find');
        
        $this->promptPjax($reuslt);
        
        $reuslt['rules'] = explode(',', $reuslt['rules']);
        
        $data = $this->getAduit();
        $this->assign('data', $data);
        $this->assign('reuslt', $reuslt);
        $this->display();
    }

    /**
     * 获取权限
     */
    private function getAduit($field = 'id,title,pid')
    {
        Tool::connect('PinYin');
        $data = AuthRuleModel::getInitnation()->getAuthGroupById($field, 'pid=0');
        $towChildren = AuthRuleModel::getInitnation()->getTwoChildren($data, $field);
        $threeChildren = AuthRuleModel::getInitnation()->getTwoChildren($towChildren, $field);
        
        Tool::connect('ArrayParse');
        // 合并孙子到爷爷做准备
        $threeChildren = AuthRuleModel::getInitnation()->getGrandFather($threeChildren);
        
        Tool::connect('Tree');
        $data = array_merge($data, (array) $towChildren, (array) $threeChildren);
        $data = Tool::makeTree($data, array(
            'parent_key' => 'pid'
        ));
        
        return $data;
    }
    // 删除组
    public function group_del()
    {
        $where['id'] = I('id');
        $m = M('auth_group');
        if ($m->where($where)->delete()) {
            $this->ajaxReturn(1);
        } else {
            $this->ajaxReturn(0);
        }
    }

    /**
     * 添加权限
     */
    public function addJurisdiction()
    {
        $_POST['pid'] = $_POST['pid'] == null ? 0 : $_POST['pid'];
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'pid'
            )
        ), true, array(
            'title',
            'pid',
            'name'
        )) ? true : $this->error('参数错误');
        (AuthRuleModel::getInitnation()->add($_POST)) ? $this->success('添加成功') : $this->error('添加失败'); // 失败
    }
    // 权限列表
    public function auth_rule()
    {
        $field = 'id,name,title,create_time,status,sort,pid';
        
        Tool::connect('PinYin');
        
        $data = AuthRuleModel::getInitnation()->getAuthGroupById($field, 'pid=0');
        
        $classData = $data;
        
        $twoChildren = AuthRuleModel::getInitnation()->getTwoChildren($data, $field);
        
        // 生成树形结构
        Tool::connect('Tree');
        
        $data = Tool::makeTree(array_merge($data, $twoChildren), array(
            'parent_key' => 'pid'
        ));
        $this->assign('data', $data); // 顶级
        $this->classData = $classData;
        $this->display();
    }
    
    // 编辑权限组
    public function auth_rule_edit()
    {
        if (! empty($_POST)) {
            $m = M('auth_rule');
            $_POST['update_time'] = time();
            $result = $m->save($_POST);
            
            if ($result !== false) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $m = M('auth_rule');
            $where['id'] = $_GET['id'];
            $result = $m->where($where)->find();
            $result['create_time'] = date('Y-m-d H:i:s', $result['create_time']);
            $this->assign('result', $result);
            $this->display();
        }
    }
    
    // 导航栏隐藏(导航栏下的侧栏也隐藏)
    public function rule_yin()
    {
        if (IS_POST) {
            $id = I('post.id');
            $auth_rule = M('auth_rule');
            $data['xianshi'] = 1;
            $result = $auth_rule->where('id=' . $id)->save($data);
            $resone = $auth_rule->where('pid=' . $id)->save($data);
        }
    }
    
    // 设置手机号
    public function set_mobile()
    {
        if (IS_POST) {
            foreach ($_POST['mobile'] as $k => $v) {
                if (empty($v)) {
                    unset($_POST['mobile'][$k]);
                }
            }
            $data['mobile'] = implode(',', $_POST['mobile']);
            
            $result = M('set')->where('id=1')->save($data);
            
            if ($result) {
                $this->success('设置成功');
            } else {
                $this->error('设置失败');
            }
        } else {
            $result = M('set')->where('id=1')->find();
            $arr = explode(',', $result['mobile']);
            $this->assign('arr', $arr);
            $this->display();
        }
    }

    /**
     * 支付设置
     */
    public function payConfig()
    {
        $this->display('pay_config');
    }

    /**
     * 通知系统
     */
    public function notification_system()
    {
        $sms_types=M('sms_type')->select();
        $this->assign('sms_types',$sms_types);
        $this->display();
    }

    /**
     * 编辑短信设置显示
     */
    public function admin_system_save()
    {
        if(IS_POST){
            $str = '';
            $config = I('post.config');
            foreach($config as $k  => $v){
                if(M('sms_check')->where('id = '.$k)->save(['status' => $v ]) < 0){
                    $str .= $k.',';
                }
            }
            if(empty($str)){
                $this->ajaxReturnData('',1);
            }
            $this->ajaxReturnData([0  =>  $str],'0');
        }
        $config = M('sms_check')->select();
        $sms_open = $config[0];
        unset($config[0]);
        $this -> assign('sms_open', $sms_open);
        $this->assign('config',$config);
        $this->display();
    }

    /**
     * ajax 将短信配置数据存入配置文件message_config并替换原来的
     */
    public function config_data_save()
    {
    $replace_config_content=M('SystemConfig')->where(array('parent_key'=>'smsConfig'))->save(array('config_value'=>serialize($_POST)));
        if(!$replace_config_content)
        $status=2;
        else {
            $status = 1;
            $url=U('admin/notification_system');
        }
        $this->ajaxReturn(array('status'=>$status,'url'=>$url));
    }

    //更新短信开启关闭状态
    public function config_status_save()
    {
        $config=unserialize(M('SystemConfig')->where(array('parent_key'=>'smsConfig'))->find()['config_value']);
        $new_array=array_merge($config,$_POST);
        //dump($config);exit;
       $save_status= M('SystemConfig')->where(array('parent_key'=>'smsConfig'))->save(array('config_value'=>serialize($new_array)));
        if(!$save_status)
        $status=2;
    else {
        $status = 1;
        $url=U('admin/notification_system');
    }
        $this->ajaxReturn(array('status'=>$status,'url'=>$url));
    }

    //渲染短信模板页面
    public function sms_template()
    {
        $sms_id = I('get.sms_id')['sms_id'];
        $arr = [];
        $tem = M('sms_template')->where(['sms_id' => $sms_id])->select();
        if($sms_id == '1'){//华信
            foreach($tem as $k  => &$v){
                $arr[$k] = unserialize($v['template']);
                $tem[$k]['message_sign'] = $arr[$k][0];
                $tem[$k]['message_content'] = $arr[$k][1];
                $tem[$k]['templcate_variable'] = $arr[$k][2];
                unset($v['template']);
            }
        }

        $sms_check = M('sms_check')->select();
        foreach($tem as $k  => $v){
            foreach($sms_check as $k2  => $v2){
                if($v['check_id'] == $v2['id']){
                    $tem[$k]['check_title'] = $v2['check_title'];
                }
            }
        }
        unset($sms_check);
        $this->assign('sms_check',$tem);
        $this->assign('sms_id',$sms_id);
        $this->display();
    }

    public function save_template_save(){
        if(IS_AJAX){
            $post = I('post.check');
            $arr = [];
            $arr2 = [];
            $str = '';//用于收集执行失败的id
            foreach($post as $k  => $v){
                $arr['check_id'] = $v['check_id'];
                $arr['sms_id'] = $v['sms_id'];

                if($v['sms_id'] == 1){//华信
                    $arr2[0] =  $v['message_sign'];
                    $arr2[1] =  $v['message_content'];
                    $arr2[2] =  $v['templcate_variable'];
                    $arr['template'] = serialize($arr2);
                }else if($v['sms_id'] == 2){//大于
                    $arr['template'] = $v['template'];
                }
                //sql
                if(empty($v['id'])){
                    //插入
                    if(M('sms_template')->add($arr) < 0){
                        $str .= $k.',';
                    }
                }else{
                    //更新
                    if(M('sms_template')->where(['id'  => $v['id']])->save($arr) < 0){
                        $str .= $k;
                    }
                }


            }
            if($str == ''){
                $this->ajaxReturnData('',1);
            }
            $this->ajaxReturnData(['error'  => $str ],0);
        }

        $status=1;
        $this->ajaxReturn(array('status'=>$status));
    }


}
