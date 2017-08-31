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
use Common\TraitClass\SearchTrait;
use Common\Tool\Tool;
use Common\Model\BaseModel;
use Admin\Model\GoodsClassModel;
use Home\Model\GoodsModel;
use Common\Tool\Event;

/**
 * 商品分类控制器 
 */
class GoodsClassController extends AuthController
{
    use SearchTrait;
   
    /**
     * 商品分类显示列表【三秒钟缓存】
     */
    public function index()
    {
        //
        $model = BaseModel::getInstance(GoodsClassModel::class);

        Tool::connect('parseString');
        $tree  = $model->buildClass();
        
        $this->parent = $tree;

        $this->model = GoodsClassModel::class;

        $this->assign('title',C('title'));
        $this->display();
    }
    
    
    
    /**
     *  无限极商品分类添加
     */
    public function add(){
        $goods_category_model = D("GoodsClass");
        if(IS_POST){
//             if($goods_category_model->create() === false){
//                 $this->error(get_error($goods_category_model));
//             }
//             if($goods_category_model->add($_POST)===false){
//                 $this->error(get_error($goods_category_model));
//             }else{
//                 $this->success("保存成功",U("index"));
//             }
            $this->addGoodsClass();
        }else{
            //取得树形结构
//             $rows=$goods_category_model->getList();
//             $this->assign("rows",$rows);
            $this->display();
        }

    }
   
    
    private function addGoodsClass()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array('hide_status'), 'fid', 'key_words','pic_url'
        ), true, array('class_name', 'description')) ? true : $this->error('参数错误');
        
        
        $classModel = BaseModel::getInstance(GoodsClassModel::class);
        
        $status = $classModel->IsExits($_POST);
        
        $this->alreadyInDataPjax($status);
        
        Tool::isSetDefaultValue($_POST, array(GoodsClassModel::$fid_d => 0));

        $status     = $classModel->add($_POST);
        
        $this->isSucess($status, U('index'));
        
    }
    
    public function getClassByNameData()
    {
        static::$keyName = 'key_words';
        
        static::$checkFauther = false;
        
        $this->getClassByName();
        
    }
    
    /**
     * 商品分类修改
     * @param integer $id 该商品分类的id
     */
    public function edit($id){
        $goods_category_model = BaseModel::getInstance(GoodsClassModel::class);
        if(IS_POST){
            if($goods_category_model->create() === false){
                $this->error(get_error($goods_category_model));
            }
            if($goods_category_model->editGoodsClass(I('post.')) === false){
                $this->error(get_error($goods_category_model));
            }else{
                $this->success("修改成功",U("index"));
            }

        }else{
          
            $classData = $goods_category_model->find($id);
            
            $this->prompt($classData);
            
            $parent    = $goods_category_model->getParentOne($classData[GoodsClassModel::$fid_d]);
            
            
            $this->assign("rows",json_encode($parent));
            $this->assign("row1",$classData);
            
            $this->assign('classModel', GoodsClassModel::class);
            
            $this->display();
        }
    }
    
    //是否推荐
    public function isRecommend ()
    {
        Tool::checkPost($_POST, array('is_numeric' => array('id')), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $status = BaseModel::getInstance(GoodsClassModel::class)->save($_POST);
        
        $this->updateClient($status, '操作');
    }
    
    /**
     * 商品分类删除
     * @param int $id 商品分类的id
     */
    public function remove($id){
        $goods_category_model = D("GoodsClass");
        if(!$goods_category_model->delGoodsClass($id)){
            $this->error("删除失败，请先删除子类");
        }else{
            $this->success("删除成功",U("index"));
        }
    }

    public function delClassGoods($id){
        $goods_category_model = D("GoodsClass");
        if(!$goods_category_model->delGoodsShop($id)){
            $this->error("删除失败");
        }else{
            $this->success("删除成功",U("index"));
        }
    }

    /**
     * 手动单击切换选项推荐
     */
    public function changType(){
        $id = I("get.id");
        $data_flag = I("get.data_flag");
        $type = I("get.type");
        $result = M("GoodsClass")->field('id')->find($id);
        if(	$data_flag == "true"){
            $result[$type] = 0;
            if(M("GoodsClass")->save($result)){
                $this->ajaxReturn("no");
            }
        }else{
            $result[$type] = 1;
            if(M("GoodsClass")->save($result)){
                $this->ajaxReturn("yes");
            }
        }
    }

    /**
     * 验证商品分类名称
     *     返回1 不可以添加
     *     返回0 可以添加
     */
    public function testCatename(){
        $class_name = I("get.cate_name");
         $re = M("GoodsClass")->where(['class_name'=>$class_name])->find();
         if($re){
             $this->ajaxReturn(['msg'=>1]);
         }else{
             $this->ajaxReturn(['msg'=>0]);
         }
    }
}

