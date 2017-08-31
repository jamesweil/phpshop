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
use Common\TraitClass\OSTrait;
use Common\Model\BaseModel;
use Admin\Model\GoodsModel;
use Admin\Model\UserModel;
use Admin\Model\ArticleModel;
use Admin\Model\OrderModel;
use Admin\Model\OrderCommentModel;
use Think\Hook;

// 权限控制类
class IndexController extends AuthController
{
    use OSTrait;
    
    // 首页
    public function index()
    {
        $m = M('auth_rule');
        $field = 'id,name,title,sort';
        $where['pid'] = 0; // 顶级ID
        $where['status'] = 1; // 显示状态
        $data = $m->field($field)
            ->where($where)
            ->order('sort')
            ->select();
        $auth = new Auth();
        // 没有权限的菜单不显示
        foreach ($data as $k => $v) {
            if (! $auth->check($v['name'], session('aid')) && session('aid') != 1) {
                unset($data[$k]);
            } else {
                // status = 1 为菜单显示状态
                $data[$k]['sub'] = $m->field($field)
                    ->where('pid=' . $v['id'] . ' AND status=1')
                    ->order('sort ASC')
                    ->select();
                $data[$k]['default_name'] = $data[$k]['sub']['0']['name'];
                $data[$k]['default_title'] = $data[$k]['sub']['0']['title'];
                foreach ($data[$k]['sub'] as $k2 => $v2) {
                    if (! $auth->check($v2['name'], session('aid')) && session('aid') != 1) {
                        unset($data[$k]['sub'][$k2]);
                    }
                }
            }
        }
        $this->assign('data', $data); // 顶级
        $this->display();
    }
    
    // 后台首页
    public function main()
    {
        $str = null;
        Hook::listen('reade', $str);
        // 获取版本信息
        $versionInfor = array();
        
        $versionInfor['shop_version'] = $this->getConfig('shop_version');
        
        $versionInfor['update_version'] = $this->getConfig('update_version');
        
        $versionInfor['company_name'] = $this->getConfig('company_name');
        
        $versionInfor['internet_url'] = $this->getConfig('internet_url');
        
        $osInfor = $this->getOSInfor();
        
        $this->assign('versionInfor', $versionInfor);

        $this->assign('sells', $this->getSells());//商品销售排行

        $this->assign('osInfor', $osInfor);
        
        $this->assign('str', $str);
        
        $this->display();
    }

    /**
     * 商城粗略信息【当天】
     */
    public function getTodayShopInformation()
    {
        $userModel = BaseModel::getInstance(UserModel::class);
        // 今日会员数
        $todayUserNumber = $userModel->getTodayDataNumber();
        
        $orderModel = BaseModel::getInstance(OrderModel::class);
        
        // 待审核 评论数
        $auditCommentNumber = (new OrderCommentModel())->getNoAudit();
        
        // 今日订单数
        $todayOrderNumber = $orderModel->getTodayDataNumber();
        
        $result = [
            'auditCommentNumber' => $auditCommentNumber,
            'todayOrderNumber' => $todayOrderNumber,
            'todayUserNumber' => $todayUserNumber
        ];
        
        $this->assign($result);
        
        $this->display('getToday');
    }

    /**
     * 商城粗略信息【全部】
     */
    public function getAllShopInformation()
    {
        $goodsCount = BaseModel::getInstance(GoodsModel::class)->getGoodsTotal();
        
        $userModel = BaseModel::getInstance(UserModel::class);
        // 会员数
        $userCount = $userModel->count();
        
        $arctileCount = (new ArticleModel())->count();
        
        $orderModel = BaseModel::getInstance(OrderModel::class);
        
        // 待审核 评论数
        $auditCommentNumber = (new OrderCommentModel())->getNoAudit();
        
        // 待处理订单数[支付的 退货的]
        $orderUntreateCount = $orderModel->getUntreatedOrderNumber();
        
        
        $result = [
            'goodsCount' => $goodsCount,
            'userCount' => $userCount,
            'arctileCount' => $arctileCount,
            'auditCommentNumber' => $auditCommentNumber,
            'orderUntreateCount' => $orderUntreateCount
        ];
        
        $this->assign($result);
        
        $this->display('getAll');
    }
    
    // 修改密码
    public function edit_pwd()
    {
        if (! empty($_POST)) {
            $m = M('admin');
            $where['id'] = session('aid');
            $where['password'] = md5(I('old_pwd'));
            $new_pwd = md5(I('new_pwd'));
            $data = $m->field('id')
                ->where($where)
                ->find();
            if (empty($data)) {
                $this->ajaxReturn(0); // 失败，原密码错误
            } else {
                $result = $m->where('id=' . $where['id'])
                    ->data('password=' . $new_pwd)
                    ->save();
                if ($result) {
                    session('aid', null);
                    session('account', null);
                    $this->ajaxReturn(1); // 修改成功
                } else {
                    $this->ajaxReturn(2); // 更新失败
                }
            }
        } else {
            $this->display();
        }
    }
    
    // 循环删除目录和文件函数
    public function delDirAndFile($dirName)
    {
        if ($handle = opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item")) {
                        delDirAndFile("$dirName/$item");
                    } else {
                        unlink("$dirName/$item");
                    }
                }
            }
            closedir($handle);
            if (rmdir($dirName))
                return true;
        }
    }
    
    // 清除缓存
    public function clear_cache()
    {
        $str = I('clear'); // 防止搜索到第一个位置为0的情况
        if ($str) {
            // strpos 参数必须加引号
            // 删除Runtime/Cache/admin目录下面的编译文件
            if (strpos("'" . $str . "'", '1')) {
                $dir = APP_PATH . 'Runtime/Cache/Admin/';
                $this->delDirAndFile($dir);
            }
            // 删除Runtime/Cache/Home目录下面的编译文件
            if (strpos("'" . $str . "'", '2')) {
                $dir = APP_PATH . 'Runtime/Cache/Home/';
                $this->delDirAndFile($dir);
            }
            // 删除Runtime/Data/目录下面的编译文件
            if (strpos("'" . $str . "'", '3')) {
                $dir = APP_PATH . 'Runtime/Data/';
                $this->delDirAndFile($dir);
            }
            // 删除Runtime/Temp/目录下面的编译文件
            if (strpos("'" . $str . "'", '4')) {
                $dir = APP_PATH . 'Runtime/Temp/';
                $this->delDirAndFile($dir);
            }
            $this->ajaxReturn(1); // 成功
        } else {
            $this->display();
        }
    }
    
    // 退出登录
    public function logout()
    {
        session('aid', null); // 注销 uid ，account
        session('account', null);
        $this->success('退出登录成功', U('Public/login'));
    }

    /**
     * 获取商品销量
     */
    public function getSells()
    {
        if(S('admin_sells')){
            return S('admin_sells');
        }
        $where = '1 = 1';
        $sql = 'SELECT o.goods_id,g.title,sum(o.goods_num) as `sum`
                FROM db_order_goods AS o
                JOIN db_goods AS g ON o.goods_id = g.id
                WHERE '.$where.'
                GROUP BY o.goods_id
                ORDER BY `sum` DESC
                LIMIT 0,10
                ';
        $data = M()->query($sql);
        S('admin_sells',$data,60);
        return $data;
    }
}