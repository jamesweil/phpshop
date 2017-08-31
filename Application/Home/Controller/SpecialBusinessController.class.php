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
use Common\Tool\Tool;
use Common\Model\BaseModel;
use Home\Model\SpecialBusinessModel;
use Home\Model\OrderModel;
use Home\Model\GoodsModel;
use Home\Model\GoodsImagesModel;
use Home\Model\GoodsClassModel;
use Home\Model\UserModel;
use Home\Model\CharacteristicPurchaseModel;
//特色业务
class SpecialBusinessController extends BaseController{
	//判断是否登录
     public function __construct()
    { 
        parent::__construct();
        
        $this->isLogin();
    }
    //企业团购
    public function enterprise_group(){
        if (IS_POST) {
            $code = I('post.code');
            $res = M('coupon_code')->where('coupon_code='.$code)->find();
            if (empty($res)){
                $this->error('兑换输入错误,请核对后重新输入!');
            }else{
                if ($res['status'] == '1') {
                    $this->error('该兑换券已兑换!');
                }else{
                    $goods_id = $res['goods_id'];
                    $order = M('order_group')->where('id='.$res['order_id'])->find();
                    if ($order['is_del'] == '0') {
                        $data['status'] = '1';//状态已领取
                        $data['user_id'] = $_SESSION['user_id'];//领取人id
                        $data['receive_time'] = time();//领取时间
                        $where['id'] = $res['id'];
                        $finally = M('coupon_code')->where($where)->save($data);
                        if ($finally === false) {
                            $this->error('修改失败!');
                        }else{
                            $order_id = $res['order_id'];//兑换券对应的团购订单id
                            $goods_id = $res['goods_id'];//兑换券对应的团购订单商品id
                            $goods = M('order_group_goods')->where(['order_id'=>$order_id,'goods_id'=>$goods_id])->find();
                            if (!empty($goods)) {
                                if ($goods['goods_num'] != '0') {
                                    $date['goods_num'] = $goods['goods_num']-1;
                                    $date['edit_time'] = time();
                                    $id = $goods['id'];
                                    $result = M('order_group_goods')->where('id='.$id)->save($date);
                                    if ($result === false) {
                                        $this->error('修改失败!');
                                    }
                                }else{
                                    $this->error('该商品已兑换完!');
                                }
                                $this->success('兑换成功!正在为您生成订单,请确认收货信息!',U('settlement',array('goods_id'=>$goods_id)));exit;
                            }
                        }
                    }else{
                        $this->error('该订单已取消!');
                    }            
                } 
            }           
        }
    	$this->display();
    }
    //结算页面
    public function settlement(){
        $goods_id = $_GET['goods_id'];
        // 获取收货人信息
        $addr_list    = D('userAddress')->getAddrByUser($_SESSION['user_id'], false);
        $this->addr_default = $addr_list[0];
        // 物流确认订单
        $this->carry = M('carry')->field('id,carry_title,carry_param')->select();

       //查询商品信息
        $this->spec    = D('goods')->spec($goods_id);
        $goods = GoodsModel::getGoodsByGoodsId($goods_id);
         //查询商品图片
        $this->img   = GoodsModel::image($goods['p_id']);        
        $this->assign('addr_list',$addr_list);
        $this->assign('goods',$goods);
        $this->display();
    }
    //兑换商品--提交订单
    public function place_order(){
        if (IS_POST) {
            $user_id = $_SESSION['user_id'];//用户id
            $goods_id = I('post.cart_id');//商品id
            $address_id = I('post.address_id');//收货地址id
            $message = I('post.message');
            $price = M('goods')->field('id,price_member')->where('id='.$goods_id)->find();
            if (!empty($price)) {
                M()->startTrans();
                $data  = [
                    'price_sum'      => $price['price_member'], // 总价
                    'address_id'     => $address_id, // 收货地址
                    'user_id'        => $user_id, // 用户
                    'create_time'    => time(),
                    'order_status'   => 1,  // 未支付
                    'comment_status' => 0,  // 未评论
                    'pay_type'       => 0, // 支付类型
                    'remarks'        => $message, // 订单备注
                    'status'         => 0,  // 订单正常
                    'translate'      => 0,  // 0,不需要发票   1,需要发票
                ];
                $model = BaseModel::getInstance(OrderModel::class);
                $retID = $model->add($data);
                if ($retID < 1) {
                    M()->rollback();
                    $this->error('提交失败!');
                }

                // 添加商品到 db_order_goods
                $data = [
                  'order_id'    => $retID,
                  'goods_id'    => $goods_id,
                  'goods_num'   => 1,
                  'goods_price' => $price['price_member'],
                  'status'      => 1,
                  'user_id'     => $user_id,
                  'ware_id'     => '',
                ];
                $ret = M('orderGoods')->add($data);
                if ($ret < 1) {
                    M()->rollback();
                    $this->error('提交失败!');
                }
                M()->commit();
                $this->success('提交成功!',U('Order/order_myorder'));exit;
            }
        }
    }
    
    //采购需求单
    public function purchase_requisition(){
        $where['user_id'] = $_SESSION['user_id'];
        //查询采购需求单列表
        $data = D('Characteristic_purchase')->getListByUser();
        $this->assign('data',$data);
    	$this->display();
    }
    //采购需求单-查询
    public function purchase_requisition_check(){
        if (IS_POST) {
            $data = D('Characteristic_purchase')->getListByCheck();
            $this->assign('data',$data);
            $this->display('purchase_requisition');
        }       
    }
    //采购需求单-详情
    public function purchase_requisition_details(){
        if (IS_POST) {
            $where['purchase_id'] = I('post.id');
            $data['state'] = 2;
            $res = M('Characteristic_purchase')->where($where)->save($data);
            if (!$res) {
                $this->ajaxReturn(0);
            }
            $this->ajaxReturn(1);
        }
        $id = I('get.id');
        //查询采购需求单表
        $Data = D('Characteristic_purchase')->getDetailsById($id);
        //查询采购需求商品表
        $data = CharacteristicPurchaseModel::getGoodsBydata($Data);
        $this->assign('data',$data);
        $this->display();
    }
    //采购需求单--提采购需求
    public function requirements(){
        M()->startTrans();
        if (IS_POST) {
            $data['purchase_title'] = I('post.purchase_title');//采购标题
            $data['purchase_type'] = I('post.purchase_type');//需求类型(1:询货 2：询价 3：询交期)
            $data['purchase_goods_id'] = implode('_',I('post.goods_id'));//采购商品id
            $data['total_price'] = I('post.total_price');//总预算
            $data['contacts'] = I('post.contacts');//联系人
            $data['tel'] = I('post.tel');//联系电话
            $data['overtime'] = strtotime(I('post.overtime'));//收货日期
            $data['pay_type'] = I('post.pay_type');//支付方式
            $data['invoice'] = I('post.invoice');//发票信息
            $data['explain'] = I('post.explain');//说明
            $data['state'] = I('post.state');//保存状态1保存 2：提交
            $data['user_id'] = $_SESSION['user_id'];//用户id
            $data['create_time'] = time();//添加时间
            $res = M('characteristic_purchase')->data($data)->add();
            if (!$res) {
                M()->rollback();
                $this->error('添加失败!');
            }
            $goods_id = I('post.goods_id');
            
            foreach ($goods_id as $key => $value) {
                $where['purchase_product_id'] = $value;
                $date['status'] = '1';
                $result = M('characteristic_purchase_product')->where($where)->save($date);
                if ($result === false) {
                    M()->rollback();
                    $this->error('添加失败!');
                }
            } 
            M()->commit();           
            $this->success('添加成功!',U('purchase_requisition'));exit;
        }
        //查询所有商品
        $goods = SpecialBusinessModel::getGoodsByProduct();
        //查询所有商品一级分类
        $class_one = D('GoodsClass')->getClassOne();
        $this->assign('class_one',$class_one);
        $this->assign('goods',$goods);
        $this->display();
    }
    //采购需求单--提采购需求--根据分类查询商品;
    public function class_goods_ajax(){       
        $class_id = I('class_id');//分类id
        $page = I('page');
        //查询县级分类
        $class = D('GoodsClass')->getClassByClassId($class_id);
        //根据分类查出分类下面的商品
        $Goods = D('Goods')->getGoodsByClassId($class_id,$page);
        //查询商品图片
        $goods = GoodsImagesModel::getGoodsImageByData($Goods['data']);
        //查询商品分类名
        $data = D('GoodsClass')->getClassNameByGoods($goods);
        $count = $Goods['count'];
        $class_id = $Goods['class_id'];
        $page = $Goods['page'];  
        $this->ajaxReturn(['page'=>$page,'data'=>$data,'class'=>$class,'count'=>$count,'class_id'=>$class_id]);
    }
    //采购需求单--提采购需求--根据分类查询上一页;
    public function goods_prve_ajax(){       
        $class_id = I('class_id');//分类id
        $page = intval(I('page'))-6;
        //查询县级分类
        $class = D('GoodsClass')->getClassByClassId($class_id);
        //根据分类查出分类下面的商品
        $Goods = D('Goods')->getGoodsByClassId($class_id,$page);
        //查询商品图片
        $goods = GoodsImagesModel::getGoodsImageByData($Goods['data']);
        //查询商品分类名
        $data = D('GoodsClass')->getClassNameByGoods($goods);
        $count = $Goods['count'];
        $class_id = $Goods['class_id'];
        $page = $Goods['page'];  
        $this->ajaxReturn(['page'=>$page,'data'=>$data,'class'=>$class,'count'=>$count,'class_id'=>$class_id]);
    }
    //采购需求单--提采购需求--根据分类查询下一页;
    public function goods_next_ajax(){       
        $class_id = I('class_id');//分类id
        $page = intval(I('page'))+6;
        //查询县级分类
        $class = D('GoodsClass')->getClassByClassId($class_id);
        //根据分类查出分类下面的商品
        $Goods = D('Goods')->getGoodsByClassId($class_id,$page);
        //查询商品图片
        $goods = GoodsImagesModel::getGoodsImageByData($Goods['data']);
        //查询商品分类名
        $data = D('GoodsClass')->getClassNameByGoods($goods);
        $count = $Goods['count'];
        $class_id = $Goods['class_id'];
        $page = $Goods['page'];  
        $this->ajaxReturn(['page'=>$page,'data'=>$data,'class'=>$class,'count'=>$count,'class_id'=>$class_id]);
    }
    //采购需求单--提采购需求--添加商品;
    public function goods_add(){
        if (IS_POST) {
            $id = I('post.danxuan');//shangp id 
            //查询商品信息
            $Goods = GoodsModel::getGoodsByGoodsId($id);
            $img = D('Goods')->image($Goods['p_id']);
            $goods = D('GoodsClass')->getClassNameByGoodsId($Goods);
            $data['user_id']        = $_SESSION['user_id'];//用户id
            $data['product_sn']     = $id;//商品id
            $data['productname']    = $goods['title'];//商品名
            $data['productclass']   = $goods['class_name'];//商品分类
            $data['productnum']     = I('post.num');//商品数量
            $data['productbudget']  = I('post.price');//预算单价
            $data['productexplain'] = I('post.productexplain');//说明
            $data['productimg']     = $img;//商品图片
            $data['checked']        = 0;//是否保存为草稿 0：草稿 1:选中
            $data['status']         = 0;//操作状态1为已使用提交
            $data['create_time']    = time();//添加时间
            $res = M('characteristic_purchase_product')->data($data)->add();
            if ($res === false) {
                $this->error('添加失败!');
            }
            $this->success('添加成功!');exit;
        }
    }
    //删除商品
    public function goods_del(){
        $where['purchase_product_id'] = I('post.id');//采购商品表id
        $res = M('characteristic_purchase_product')->where($where)->delete();
        if (!$res) {
            $this->ajaxReturn(0);//失败
        }
        $this->ajaxReturn(1);//成功
    }
    //加盟申请
    public function join_application(){
        if (IS_POST) {
            $data['applicant'] = I('post.applicant');//申请人
            $data['tel'] = I('post.tel');//联系方式
            $data['age'] = I('post.age');//年龄
            $data['email'] = I('post.email');//联系邮箱
            $data['province'] = I('post.province');//省份
            $data['city'] = I('post.city');//城市
            $data['county'] = I('post.area');//地区
            $data['address'] = I('post.address');//详细地址
            $data['fax'] = I('post.fax');//传真
            $data['qq'] = I('post.qq');//QQ
            $data['remark'] = I('post.remark');//备注说明
            $data['user_id'] = $_SESSION['user_id'];//用户id
            $data['application_time'] = time();//申请时间
            $result = M('apply')->where('user_id='.$data['user_id'])->find();
            if (empty($result)) {
                $res = M('apply')->data($data)->add();
                if ($res === false) {
                    $this->error('申请失败!');
                }
                $this->success('申请成功,请等待审核!');exit;
            } 
            $this->error('对不起!您已经申请过了!');
        }
    	$this->display();
    }
    //打印机租赁
    public function printer_rental(){
        $res = SpecialBusinessModel::getPrinterRentalByUser();
        $goods = GoodsModel::getGoodsByData($res['res']);        
        
        //查询当月抄表记录
        $meter = SpecialBusinessModel::getMonthMeterByData($goods);
        $this->data = GoodsImagesModel::getGoodsImageByData($meter);
        $this->page = $res['page'];
    	$this->display();
    }
    //打印机租赁--申请退回定金详细
    public function returnDetails(){
       // var_dump(strtotime("2017-03-13")); 
        $id = $_GET['id'];//打印机租赁id
        //查询打印机
        $data = SpecialBusinessModel::getPrinterRentalByPrinterId($id);
        $Goods = GoodsModel::getGoodsByGoodsId($data['goods_id']);
        $goods =  GoodsimagesModel::getGoodsImageByGoods($Goods);
        $this->assign('goods',$goods);
        $this->assign('data',$data);
        //查询最近抄表记录
        $meter = SpecialBusinessModel::getRecentMeterReadingByPrinterId($id);      
        $this->assign('meter',$meter);
        //查询退回订单记录
        $deposit = SpecialBusinessModel::getDepositDetailsByPrinterId($id);
        $this->assign('deposit',$deposit);
        $this->display();
    }
    //打印机租赁--补充耗材
    public function supplies(){
        if (IS_POST) {
            $consumables = I('post.consumables');
            $num  = I('post.num');
            $stateMap = array_combine($consumables,$num);
            foreach ($stateMap as $key => $value) {
                $data['consumables'] = $key;
                $data['num'] = $value;
                $data['user_id'] = $_SESSION['user_id'];//用户id
                $data['add_time'] = time();//添加时间
                $data['printer_id'] = I('post.id');//打印机租赁id
                $data['remark'] = I('post.remark');//备注说明
                $res = M('supplementary_supplies')->data($data)->add();
            }
            if ($res) {
                $this->success('提交成功!请等待商家发货!');exit;
            }
            $this->error('提交失败!');
        }
        $id = $_GET['id'];//打印机租赁id
        $this->assign('id',$id);
        $this->display();
    }
    
    //打印机租评--补充耗材记录
    public function Record(){
        $user_id = $_SESSION['user_id'];
        $printer_id = $_GET['id'];//打印机租赁id
        $data = SpecialBusinessModel::getRecordByUserId($user_id,$printer_id);
        $this->assign('data',$data);
        $this->display();
    }
    //打印机租赁--合同详情
    public function contractDetails(){
        $id = $_GET['id'];//打印机租赁id
        //查询打印机
        $data = SpecialBusinessModel::getPrinterRentalByPrinterId($id);
        $Goods = GoodsModel::getGoodsByGoodsId($data['goods_id']);
        $goods =  GoodsimagesModel::getGoodsImageByGoods($Goods);
        //查询最近抄表记录
        $meter = SpecialBusinessModel::getRecentMeterReadingByPrinterId($id);      
        $this->assign('meter',$meter);
        $this->assign('goods',$goods);
        $this->assign('data',$data);
        $this->display();
    }
    //打印机租赁--继续租赁
    public function continued_lease(){
        $this->display();
    }
    //打印机租赁--申请退回押金
    public function apply_for_deposit(){
        M()->startTrans();
        $id = I('post.id');//打印机租赁表id
        $where['id'] = $id;
        $data['status'] = '2';
        $res = M('printer_rental')->where($where)->save($data);
        if (!$res) {
            M()->rollback();
            $this->ajaxReturn(0);
        }
        $date['printer_id'] = $id;
        $date['add_time'] = time();
        $result = M('printer_apply_for_deposit')->data($date)->add();
        if (!$result) {
            M()->rollback();
            $this->ajaxReturn(0);
        }
        M()->commit();
        $this->ajaxReturn(1);
    }
    //打印机租赁--抄表记录
    public function public_payment(){
        $id = $_GET['id'];//打印机租赁id
        $data = SpecialBusinessModel::getRecentMeterAllByPrinterId($id);
        $this->assign('data',$data);
        $this->display();
    }
    //打印机租赁==查询
    public function printer_query(){
        $res = SpecialBusinessModel::getPrinterRentalByQuery($_POST);
        $goods = GoodsModel::getGoodsByData($res['res']);        
        
        //查询当月抄表记录
        $meter = SpecialBusinessModel::getMonthMeterByData($goods);
        $this->data = GoodsImagesModel::getGoodsImageByData($meter);
        $this->page = $res['page'];
        $this->display('printer_rental');
    }
}