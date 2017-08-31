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
use Home\Model\OrderModel;
use Common\Tool\Tool;
use Home\Model\OrderGoodsModel;
use Home\Model\OrderCommentModel;  
use Home\Model\GoodsModel;
use Home\Model\GoodsImagesModel;
use Home\Model\GoodsClassModel;
use Common\Model\UserAddressModel;
use Common\Model\BaseModel;
use Home\Model\CouponListModel;
use Home\Model\AssetsModel;
use Home\Model\UserModel;
use Home\Model\FootPrintModel;
use Upload\Controller\UploadController;
use Common\TraitClass\SmsVerification;

//资产中心
class AssetsController extends BaseController{
	//判断是否登录
     public function __construct()
    {
        parent::__construct();
        
        $this->isLogin();
    }
    //余额
    public function balance(){
        //查询余额
        $this->balance = AssetsModel::getBalanceByUserId();

        //查询近三个月支付记录
        $this->data = Assetsmodel::getNearPayByUserId();
        $this->status = 1;
    	$this->display();
    }
    //ajax查询三个月前支付记录
    public function balance_front(){
        if (empty($_GET['p'])) {
            if ($_GET['flag'] == 2) {
                $data = AssetsModel::getFrontPayByUserId();
                $this->ajaxReturn($data);
            }else{
                $data = AssetsModel::getNearPayByUserId();
                $this->ajaxReturn($data);
            }  
        }else{
            if ($_GET['flag'] == 2) {
                $this->balance = AssetsModel::getBalanceByUserId();
                //查询三个月前支付记录
                $this->data = Assetsmodel::getFrontPayByUserId();
                $this->status = 2;
                $this->display('balance');
            }else{
                //查询余额
                $this->balance = AssetsModel::getBalanceByUserId();
                //查询近三个月支付记录
                $this->data = Assetsmodel::getNearPayByUserId();
                $this->status = 1;
                $this->display('balance');
            }  
        }
                 
    }
    //优惠券
    public function coupon(){
        //查询可用优惠券
        $this->usable = CouponListModel::getUsableCouponByUserId();
        //查询已用优惠券
        $this->used = CouponListModel::getUsedCouponByUserId();
        
        //查询过期优惠券
        $this->overdue = CouponListModel::getOverdueCouponByUserId(); 
    	$this->display();
    }
    //删除优惠券
    public function coupon_del(){
        $where['id'] = I('post.id');//用户领取的优惠券id
        $res = M('coupon_list')->where($where)->delete();
        if (!$res) {
            $this->ajaxReturn(0);//删除失败
        } else {
            $this->ajaxReturn(1);//删除成功
        }
        
    }
    //我的收藏
    public function myCollection(){
        //查询收藏表细信息
        $goods = AssetsModel::getCollectionWholeByUserId();
        //查询对应商品信息
        $Goods = GoodsModel::getGoodsByData($goods['res']);
        //查询商品图片
        $data = GoodsImagesModel::getGoodsImageByData($Goods);
        //查询我的收藏总数
        $where['user_id'] = $_SESSION['user_id'];
        $this->count =  M('Collection')->where($where)->count();
        //查询我的收藏降价商品总数
        $date['user_id'] = $_SESSION['user_id'];
        $date['status'] = 1;
        $this->price_count =  M('Collection')->where($date)->count();
        $this->assign('data',$data);
        $this->assign('status',0);
        $this->page = $goods['page'];
    	$this->display();
    }
    //我的收藏ajax搜索商品
    public function ajax_goods(){
        
        $name = $_POST['search'];
        $goods = AssetsModel::getGoodsBySearch($name);
        //查询对应商品信息
        $Goods = GoodsModel::getGoodsByData($goods);
        //查询商品图片
        $data = GoodsImagesModel::getGoodsImageByData($Goods);
        //查询我的收藏总数
        $where['user_id'] = $_SESSION['user_id'];
        $this->count =  M('Collection')->where($where)->count();
        //查询我的收藏降价商品总数
        $date['user_id'] = $_SESSION['user_id'];
        $date['status'] = 1;
        $this->price_count =  M('Collection')->where($date)->count();
        $this->assign('data',$data);
        $this->page = $goods['page'];
        $this->display('myCollection');     
    }
    //我的收藏-降价商品
    public function collection_price(){
        //查询收藏表细信息
        $goods = AssetsModel::getCollectionPriceByUserId();
        //查询对应商品信息
        $Goods = GoodsModel::getGoodsByData($goods['res']);
        //查询商品图片
        $data = GoodsImagesModel::getGoodsImageByData($Goods);
        //查询我的收藏总数
        $where['user_id'] = $_SESSION['user_id'];
        $this->count =  M('Collection')->where($where)->count();
        //查询我的收藏降价商品总数
        $date['user_id'] = $_SESSION['user_id'];
        $date['status'] = 1;
        $this->price_count =  M('Collection')->where($date)->count();
        $this->assign('data',$data);
        $this->page = $goods['page'];
        $this->display('myCollection');
    }
    //删除我的收藏
    public function collection_del(){
        $m  = M('Collection');
        $id = I('post.id');
        $res = $m->where('id='.$id)->delete();
        if (!$res) {
            $this->ajaxReturn(0);
          }  
        $this->ajaxReturn(1);
    }
    //我的积分
    public function integral(){
        //$overdue = $this->getConfig('overdue');//按天算

        // //查询个人积分 getGoodsByChildrenOrderData
        //$integral = UserModel::getIntegralByUserId();//
        //$integral['overdue'] = $integral['update_time']+$overdue*60*60*24;
        $integral = $this->user_integral();
        //查询个人积分使用情况
        $Data = AssetsModel::getIntegralUseByUserId();
        
        //查询使用(增加)商品信息
        $Goods  = GoodsModel::getInitnation()->getGoodsByData($Data['res']); 
        $data = GoodsImagesModel::getGoodsImageByData($Goods);
        $this->assign('integral',$integral);
        $this->assign('data',$data);
        $this->assign('type',1); 
        $this->assign('page',$Data['page']);
    	$this->display();
    }
    //查询我的积分-支出
    public function integral_expen(){
        
         //过期时间
        //$overdue = $this->getConfig('overdue');//按天算
        $integral = $this->user_integral();


        // //查询个人积分 getGoodsByChildrenOrderData
        //$integral = UserModel::getIntegralByUserId();//
        //$integral['overdue'] = $integral['update_time']+$overdue*60*60*24;
        $type = 2;
        $Data = AssetsModel::getIntegralUseByUserId($type);
        //查询使用(增加)商品信息
        $Goods  = GoodsModel::getInitnation()->getGoodsByData($Data['res']);
        $data = GoodsImagesModel::getGoodsImageByData($Goods);
        $this->assign('integral',$integral);
        $this->assign('data',$data);
        $this->assign('type',3);
        $this->assign('page',$Data['page']);
        $this->display('integral');
    }
     //查询我的积分-收入
    public function integral_use(){

       //$overdue = $this->getConfig('overdue');//按天算


        // //查询个人积分 getGoodsByChildrenOrderData
        //$integral = UserModel::getIntegralByUserId();//
        //$integral['overdue'] = $integral['update_time']+$overdue*60*60*24;
        $integral = $this->user_integral();

        $type = 1;
        $Data = AssetsModel::getIntegralUseByUserId($type);
        //查询使用(增加)商品信息
        $Goods  = GoodsModel::getInitnation()->getGoodsByData($Data['res']);
        $data = GoodsImagesModel::getGoodsImageByData($Goods);
        $this->assign('integral',$integral);
        $this->assign('data',$data);
        $this->assign('type',2);
        $this->assign('page',$Data['page']);
        $this->display('integral');
    }
    //积分兑换
    public function punkte(){
        $Goods = AssetsModel::getGoodsByPunkte();
        $Data = GoodsModel::getGoodsByData($Goods['res']);
        $data = GoodsImagesModel::getGoodsImageByData($Data); 
        $page = $Goods['page'];
        $this->assign('data',$data);
        $this->assign('page',$page);
    	$this->display();
    } 
    //ajax积分兑换
    public function punkte_ajax(){
        switch ($_GET['flag']) {
            case '1':
            $range = array('ELT',1000);
            break;
            case '2':
            $range = array(array('EGT',1000),array('ELT',2000),'AND');
            break;
            case '3':
            $range = array(array('EGT',2000),array('ELT',3000),'AND');
            break;
            case '4':
            $range = array(array('EGT',3000),array('ELT',5000),'AND');
            break;
            case '5':
            $range = array(array('EGT',5000),array('ELT',10000),'AND');
            break;
            case '6':
            $range = array(array('EGT',10000),array('ELT',20000),'AND');
            break;
            case '7':
            $range = array('EGT','20000');
            break;
            default: break;
        }
        $Goods = AssetsModel::getGoodsByPunkte($range);
        $Data = GoodsModel::getGoodsByData($Goods['res']);
        $data = GoodsImagesModel::getGoodsImageByData($Data); 
        $page = $Goods['page'];
        $this->assign('data',$data);
        $this->assign('page',$page);
        $this->display('punkte');
    }
    //我购买过的产品
    public function gekauft(){
        Tool::connect('parseString');
        $orderData = $this->getOrder(array($_SESSION['user_id'], OrderModel::ReceivedGoods), ' and order_status ="%s"');
        
        $data = $orderData['data'];
        $page = $orderData['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
    	$this->display();
    }
    //我的足迹
    public function myTracks(){
        $where['user_id'] = $_SESSION['user_id'];
        $data = GoodsClassModel::getClass();
        $foot = FootPrintModel::getTracksByUserId($where);
        $Goods = GoodsModel::getGoodsByData($foot['res']);
        $goods = GoodsImagesModel::getGoodsImageByData($Goods);
        $page = $foot['page']; 
        $this->assign('data',$data);
        $this->assign('goods',$goods);
        $this->assign('page',$page);
    	$this->display();
    }
    //删除单个我的足迹
    public function myTracks_del(){
        $id = I('post.id');
        $m = M('foot_print');
        $res = $m->where('id='.$id)->delete();
        if (!$res) {
           $this->ajaxReturn(0); 
        } 
        $this->ajaxReturn(1);
    }
    //删除全部我的足迹
    public function myTracks_del_all(){
        $user_id = $_SESSION['user_id'];
        $m = M('foot_print');
        $res = $m->where('uid='.$user_id)->delete();
        if (!$res) {
           $this->ajaxReturn(0); 
        } 
        $this->ajaxReturn(1);
    }
    //足迹分类
    public function myTracks_class(){
        $where['user_id'] = $_SESSION['user_id'];
        $class_id = I('class_id');//分类id
        $date = GoodsClassModel::getClassIdByFid($class_id);
        if (!empty($date)) {
            $where['class_id'] = array('IN',implode(',',$date));
        }else{
            $where['class_id'] = $class_id;
        }
        //查询足迹表信息;
        $goods = FootPrintModel::getMyTracksByClassId($where);
        $data = GoodsClassModel::getClass();
        $class_name = GoodsClassModel::getClassNameByClassId($class_id);
        $this->assign('class_name',$class_name);
        $this->assign('goods',$goods);
        $this->assign('data',$data);
        $this->display('myTracks');
    
    }
    //找相似
    public function find_similar(){
        
    }
    //我的评价
    public function myComment(){
        // //查询订单表待评价数据
        $order = $this->getOrder(array($_SESSION['user_id'],0,0, OrderModel::ReceivedGoods), 'and status ="%s" and comment_status ="%s" and order_status ="%s"');
        // 获取订单收货人信息
        $data = UserAddressModel::getUserAddressByData($order['data']);
        $count = $order['count']; 
        $page = $order['page'];
        //查询待晒单数据
        $Waiting = OrderCommentModel::getWaitingListByComment();
        $w_count = $Waiting['count'];
        //查询订单信息
        $Order = OrderModel::getOrderByData($Waiting['res']);
        //查询对应的商品信息
        $WaitingGoods = GoodsModel::getGoodsByData($Order);
        //查询商品对应的图片
        $Waitinggoods = GoodsImagesModel::getGoodsImageByData($WaitingGoods);
        $Waiting_goods  = OrderCommentModel::getCommentFeelByComment($Waitinggoods);
        //查询已评价商品列表
        $Already = OrderCommentModel::getAlreadyCommentByComment();
        //查询对应的商品信息
        $AlreadyGoods = GoodsModel::getGoodsByData($Already);
        //查询商品对应的图片
        $Alreadygoods = GoodsImagesModel::getGoodsImageByData($AlreadyGoods);
// showData($data,1);
        $this->assign('data',$data);
        $this->assign('count',$count);
        $this->assign('w_count',$w_count);
        $this->assign('Waiting_goods',$Waiting_goods);
        $this->assign('Alreadygoods',$Alreadygoods);
        $this->assign('page',$page);
        $this->display();
    }
    //我的评价-查看评论
    public function comment_check(){
        $id = I('get.id');//评论id
        //查询评论表信息
        $comment = OrderCommentModel::getCommentByCommentId($id);
        //查询对应的评论标签
        $Goods = OrderCommentModel::getCommentFeelByCommentId($comment);
        //查询对应的商品信息
        $goods = OrderModel::getGoodsNameByOrderGoods($Goods);        
        //查询对应的商品图片
        $data = GoodsImagesModel::getGoodsImageByGoods($goods);
        $this->assign('data',$data); 
        $this->display();
    }
   /**
     * 上传图片
     * @return [type] [description]
     */
    public function uploadImage(){
        M()->startTrans();
        if (IS_POST) {
            if ($_FIELS['file']['error'] == 0) {
                $upload = new \Think\Upload($this->config);// 实例化上传类
                //上传文件
                $info = $upload->upload();         
                if(!$info) {        // 上传错误提示错误信息
                    $this->error($upload->getError());
                }
                foreach ($info as $key => $value) {
                    $data['path'] = '/'.Uploads.'/'.$value['savepath'].$value['savename'];
                    $data['create_time']  = time();
                    $res = M('Images')->data($data)->add();
                    if (!$res) {
                        M()->rollback();
                        $this->error('晒图失败!');
                    }
                    $show_pic .= $res.',';              
                }
                $where['id'] = I('post.id');
                $data['anonymous'] = I('post.anonymous');
                $data['show_pic'] = substr($show_pic,0,-1);
                $res = M('OrderComment')->where($where)->save($data);
                if (!$res) {
                    M()->rollback();
                    $this->error('晒图失败!');
                }
                M()->commit();
                $this->success('晒图成功!');exit;
            }
        }       
    }
    /**
     * 辅助方法
     */
    private  function getOrder(array $value, $where)
    {
        //实例化订单模型 [懂了吗]
        $baseModel = BaseModel::getInstance(OrderModel::class);
        
        $Order = $baseModel->getOrderByUser($value, $where);
        $data = OrderModel::getFreightByData($Order['data']);        
        Tool::connect('parseString');       
        //获取订单商品信息       
        $goodsData = OrderGoodsModel::getInitnation()->getGoodsInfoByOrder($data);
       
        //传递商品模型
        //传递给商品表
        $goods  = GoodsModel::getInitnation()->getGoodsByChildrenOrderData($goodsData);

        //组合数据        
        $orderData = Tool::parseTwoArray($data, $goods, 'order_id', array('goods'));
        $page = $Order['page'];
        $count = $Order['count'];
        return array('data'=>$orderData,'page'=>$page,'count'=>$count);
    }
    //图片上传属性设置
    protected $config = array(
        'mimes'         =>  array(), //允许上传的文件MiMe类型
        'maxSize'       =>  3145728, //上传的文件大小限制 (0-不做限制)
        'exts'          =>  'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'       =>  true, //自动子目录保存文件
        'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath'      =>  './Uploads/', //保存根路径
        'savePath'      =>  'show/', //保存路径
        'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
        'replace'       =>  false, //存在同名是否覆盖
        'hash'          =>  true, //是否生成hash编码
        'callback'      =>  false, //检测文件是否存在回调，如果存在返回文件信息数组
        'driver'        =>  '', // 文件上传驱动
        'driverConfig'  =>  array(), // 上传驱动配置
    );
    //查询商品
    public function ajax_car_goods(){
        $name = I('name');
        $where['title'] = array('like',"%".$name."%");
        $where['p_id'] = array('neq','0');
        $res = M('Goods')->field('id,title')->where($where)->select();
        if (!empty($res)) {
            $this->ajaxReturn($res);
        }else{
            $this->ajaxReturn('');
        }
    }

    public function user_integral()
    {
        $this->key = 'integral';
        $overdue = $this->getGroupConfig()['Integral_time'];//按天算
        $total = D('integralUse')->valid($_SESSION['user_id']);
        $info= M('integralUse')->field('integral,trading_time,used')->where([
            'user_id' => $_SESSION['user_id'],
            'type' => '1',
            'status' => '1'
        ])->order('trading_time asc')->find();
        $integral['overdue_integral'] = $info['integral'] - $info['used'];
        $integral['overdue'] = $overdue*60*60*24 - (time() - $info['trading_time']) + time();
        $integral['integral'] = $total;
        return $integral;
    }
}