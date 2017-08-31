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
use Home\Model\GoodsModel;
use Common\Model\UserAddressModel;
use Common\Model\BaseModel;
use Home\Model\GoodsImagesModel;
use Home\Model\ServiceModel;
use Home\Model\AssetsModel;
use Think\Page;
use Upload\Controller\UploadController;
use Home\Model\DoorRepairModel;
//客服服务
class ServiceController extends BaseController{
	//图片上传属性设置
    protected $config = array(
        'mimes'         =>  array(), //允许上传的文件MiMe类型
        'maxSize'       =>  3145728, //上传的文件大小限制 (0-不做限制)
        'exts'          =>  'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'       =>  true, //自动子目录保存文件
        'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath'      =>  './Uploads/', //保存根路径
        'savePath'      =>  'voucher/', //保存路径
        'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
        'replace'       =>  false, //存在同名是否覆盖
        'hash'          =>  true, //是否生成hash编码
        'callback'      =>  false, //检测文件是否存在回调，如果存在返回文件信息数组
        'driver'        =>  '', // 文件上传驱动
        'driverConfig'  =>  array(), // 上传驱动配置
    );
    //申请售后(退货)
    public function return_goods() { 
        //
        $goods_id = I('get.goods_id');//商品id
        $order_id = I('get.order_id');//订单id
        //查询商品订单表
        $order_goods = OrderGoodsModel::getOrderGoodsByGoodsId($goods_id,$order_id);
        //查询订单表数据
        $order = OrderModel::getOrderByOrderId($order_id);
       
        //查询订单商品表对应的商品信息
        $Goods = OrderModel::getGoodsNameByOrderGoods($order_goods);        
        //查询商品图片
        $goods = GoodsImagesModel::getGoodsImageByGoods($Goods);      
        //商品总价
        $goods['goods_price_sum'] = $goods['goods_price']*$goods['goods_num'];
        //查询用户用过的优惠券
        $Coupon = D('CouponList')->getCouponByOrderId($order_id);
        $coupon = D('Coupon')->getCouponDetailsById($Coupon['c_id']);
        $this->assign('coupon',$coupon);
        $this->assign('order',$order);
        $this->assign('goods',$goods);
        $this->display();
    }

    //提交退货申请
    public function return_goods_add(){
        $m = M('order_return_goods');
        if (IS_POST) { 
            $where['goods_id'] = I('post.goods_id');//商品id
            $where['order_id'] = I('post.order_id');//商品id
            $result = M('order_return_goods')->where($where)->find();
            if (!empty($result)) {
                $this->error('该商品申请退货已经提交!');
            } else{          
                $upload = new \Think\Upload($this->config);// 实例化上传类
                //上传文件
                $info = $upload->upload();         
                if(!$info) {        // 上传错误提示错误信息
                    $this->error($upload->getError());
                }else{      // 上传成功
                    foreach ($info as $key => $value) {
                        $voucher .= '/'.Uploads.'/'.$value['savepath'].$value['savename'].',';
                    }
                    $data['voucher'] = substr($voucher,0,-1);
                    $data['tuihuo_case'] = I('post.tuihuo_case');//退货原因
                    $data['order_id'] = I('post.order_id');//订单id
                    $data['goods_id'] = I('post.goods_id');//商品id
                    $data['explain'] = I('post.explain');//退货说明
                    $data['price'] = I('post.price');//退款金额
                    $data['type'] = 1;//
                    $data['create_time'] = time();//添加时间
                    $data['user_id'] = $_SESSION['user_id'];//用户id
                    $data['status'] = 0;//状态
                    $data['apply_img'] = '';
                    $res=$m->data($data)->add();
                    if ($res) {
                        $goods_id = I('post.goods_id');//商品id
                        $order_id = I('post.order_id');//商品id
                        M('order_goods')->where(['goods_id'=>I('post.goods_id'),'order_id'=>I('post.order_id')])->setField('status','5');
                        $this->success('申请成功',U('return_waitfor',array('id'=>$res,'goods_id'=>$goods_id,'order_id'=>$order_id)));exit;
                    }
                    $this->error('申请失败');
                }
            }
        }
    } 
    //申请退款 
    public function return_price_add(){
        $m = M('order_return_goods');
        if (IS_POST) { 
            $where['goods_id'] = I('post.goods_id');//商品id
            $where['order_id'] = I('post.order_id');//商品id
            $result = M('order_return_goods')->where($where)->find();
            if (!empty($result)) {
                $this->error('该商品申请退款已经提交!');
            } else{
                 $upload = new \Think\Upload($this->config);// 实例化上传类
                //上传文件
                $info = $upload->upload();         
                if(!$info) {        // 上传错误提示错误信息
                    $this->error($upload->getError());
                }else{      // 上传成功
                    foreach ($info as $key => $value) {
                        $voucher .= '/'.Uploads.'/'.$value['savepath'].$value['savename'].',';
                    }
                    $data['voucher'] = substr($voucher,0,-1);
                    $data['tuihuo_case'] = I('post.tuihuo_case');//退货原因
                    $data['order_id'] = I('post.order_id');//订单id
                    $data['goods_id'] = I('post.goods_id');//商品id
                    $data['explain'] = I('post.explain');//退货说明
                    $data['price'] = I('post.price');//退款金额
                    $data['is_receive'] = I('post.is_receive');//退货时是否收到货
                    $data['type'] = 2;//
                    $data['create_time'] = time();//添加时间
                    $data['user_id'] = $_SESSION['user_id'];//用户id
                    $data['status'] = 0;//状态
                    $data['apply_img'] = '';
                    $res=$m->data($data)->add();
                    if ($res) {
                        $goods_id = I('post.goods_id');//商品id
                        $order_id = I('post.order_id');//商品id
                        M('order_goods')->where(['goods_id'=>I('post.goods_id'),'order_id'=>I('post.order_id')])->setField('status','5');
                        $this->success('申请成功',U('return_waitfor',array('id'=>$res,'goods_id'=>$goods_id,'order_id'=>$order_id)));exit;
                    }
                    $this->error('申请失败');
                }
            }         
        }
    } 
    //申请等待中
    public function return_waitfor(){
        $id = I('id');
        $goods_id = I('goods_id');//商品id\
        $order_id = I('order_id'); 
        //查询商品订单表
        $order_goods = OrderGoodsModel::getOrderGoodsByGoodsId($goods_id,$order_id);
        //查询订单表数据
        $order = OrderModel::getOrderByOrderId($order_id);
        //查询订单商品表对应的商品信息
        $Goods = OrderModel::getGoodsNameByOrderGoods($order_goods);        
        //查询商品图片
        $goods = GoodsImagesModel::getGoodsImageByGoods($Goods);       
        //商品总价
        $goods['goods_price_sum'] = $goods['goods_price']*$goods['goods_num'];
        //查询用户用过的优惠券
        $Coupon = D('CouponList')->getCouponByOrderId($order_id);
        $coupon = D('Coupon')->getCouponDetailsById($Coupon['c_id']);
        $this->assign('coupon',$coupon);
        $this->assign('order',$order);
        $this->assign('goods',$goods);
        $this->assign('id',$id);
        $this->display();
    } 
    //查看退单
    public function check_list(){
        if (!empty($_GET['id'])) {      
            $id = I('id');//订单id
            //查询退货订单
            $data = OrderModel::getCheckByOrderId($id);
            $this->assign('data',$data);
            $this->display();
        }
    }
    //查看退单详情
    public function check_detail(){
        if (!empty($_GET['id'])) {      
            $id = I('id');//退单id
            //查询退货订单
            $data = OrderModel::getCheckDetailByOrderId($id);
            $this->assign('data',$data);
            $this->display();
        }
    }
    //投诉卖家
    public function report(){
        if (IS_POST) {
            $data['reason']   = I('post.reason');//投诉原因
            $data['content']  = I('post.content');//投诉内容
            $data['goods_id'] = I('post.goods_id');//商品id
            $data['user_id']  = $_SESSION['user_id'];//用户id
            $data['time']     = time();
            $res = M('report')->data($data)->add();
            if ($res === false) {
                $this->error('提交失败!');
            }
            $this->success('提交成功!');exit;
        }
        $goods_id = I('get.goods');//商品id
        $this->assign('goods_id',$goods_id);
        $data = ServiceModel::getReportByUserId();
        $this->assign('data',$data);
        $this->display();
    }
    //投诉中心
    public function report_center(){
        $data = ServiceModel::getReportByUserId();
        $this->assign('data',$data);
        $this->display();
    }
    //售后管理
    public function after_sale(){
        $data = ServiceModel::getRepairByUserId();
        $this->assign('data',$data);
        $this->display();
    }
    //售后管理 查看详情
    public function after_sale_details(){
        $id = $_GET['id'];//售后id
        $data = ServiceModel::getRepairDetailById($id);
        $this->assign('data',$data);
        $this->display();
    }
    //上门维修选择
    public function repair_choice(){
        $this->display();
    }
    //商城商品上门维修
    public function repair_ys(){
        if (IS_POST) {
            $data['repair_project'] = I('post.tuihuo_case');//维修项目
            $data['repair_address'] = I('post.address');//维修地点
            $data['tel'] = I('post.tel');//联系电话
            $data['describe'] = I('post.explain');//详细描述
            $data['add_time'] = time();//申请时间
            $data['is_ys'] = 1;//商城商品
            $data['user_id'] = $_SESSION['user_id'];//用户id
            $res = M('door_repair')->data($data)->add();
            if ($res === false) {
                $this->error('申请失败,请重新申请!');
            }
            $this->success('申请成功!');exit; 
        }
        $this->display();
    }
    //非商城上门维修
    public function repair(){
        if (IS_POST) {
            $data['repair_project'] = I('post.tuihuo_case');//维修项目
            $data['repair_time'] = strtotime(I('post.update_time'));//维修时间
            $data['repair_address'] = I('post.address');//维修地点
            $data['tel'] = I('post.tel');//联系电话
            $data['add_time'] = time();//申请时间
            $data['is_ys'] = 2;//非商城商品
            $data['user_id'] = $_SESSION['user_id'];//用户id
            $res = M('door_repair')->data($data)->add();
            if ($res === false) {
                $this->error('申请失败,请重新申请!');
            }
            $this->success('申请成功!');exit;
        }
        $this->display();
    }
    //网站公告
    public function announcement(){
        $data = ServiceModel::getAnnouncement();
        $this->assign('data',$data);
        $this->display();
    }
    //网站公告详情
    public function announcement_details(){
        $id = $_GET['id'];//公告id
        $data = ServiceModel::getAnnouncementDetailsById($id);
        $this->assign('data',$data);
        $this->display();
    }
    //意见建议
    public function opinion(){
        $Goods = ServiceModel::getOrderByUser();
        //查询对应商品信息
        $goods = GoodsModel::getGoodsByData($Goods);
        //查询商品图片
        $img = GoodsImagesModel::getGoodsImageByData($goods);
        $count= count($img);
        $page = new \Think\Page($count,5);
        $data = array_slice($img,$page->firstRow,$page->listRows);
        $page = $page->show();
        $this->assign('page',$page);
        $this->assign('data',$data);
        $this->display();
    }
    //咨询回复
    public function advisoryReply(){
        //查询用户提过所有的问题
        $problem = ServiceModel::getProblemByUser();
        //构成寻对应的答案
        $data = ServiceModel::getAnswerByProblem($problem);
        $this->assign('data',$data);
        $this->display();
    }
     //返修退换货
    public function return_repair(){
        $data = ServiceModel::getReturnRepairByUser();
        $this->assign('data',$data);
        $this->display();
    }
    //上门维修记录
    public function door_maintenance_record(){
        $data = D('DoorRepair')->getListByUserId();
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * 公告列表
     */
    public function getAnnounceList(){
        $announModel = M("Announcement");
        $count = $announModel->where(['status'=>1])->count();
        $page_setting = C('ANNOUNCE_PAGE');
        $page = new Page($count, $page_setting);
        $page_show = $page->show();
        $rows = $announModel->field("id,title,create_time")->where(['status'=>1]) ->order("id desc") ->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('announces',$rows);
        $this->assign('page_show',$page_show);
        $this->display();
    }

    /**
     *  公告详情
     */
    public function getAnnouDetails(){
        $id = $_GET['id'];//公告id
        $data = ServiceModel::getAnnouncementDetailsById($id);
        $this->assign('data',$data);
        $this->display();

   }
}