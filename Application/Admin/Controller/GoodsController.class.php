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
use Admin\Model\BrandModel;
use Admin\Model\GoodsAttributeModel;
use Admin\Model\GoodsClassModel;
use Common\Model\GoodsDetailModel;
use Admin\Model\GoodsImagesModel;
use Admin\Model\GoodsModel;
use Admin\Model\GoodsSpecItemModel;
use Admin\Model\GoodsSpecModel;
use Admin\Model\HotWordsModel;
use Admin\Model\SpecGoodsPriceModel;
use Common\Controller\AuthController;
use Common\Model\BaseModel;
use Common\Tool\Tool;
use Think\Page;
use Common\TraitClass\SearchTrait;
use Common\TraitClass\ThumbNailTrait;
use Common\TraitClass\SmsVerification;
use Common\TypeParse\AbstractParse;

/**
 * 商品后台管理
 * @author Administrator
 */
class GoodsController extends AuthController
{
    use SearchTrait;
    use ThumbNailTrait;
    use SmsVerification;
	/**
	 * 商品列表
	 */
    public function goods_list()
    {
		// 关键词搜索
        $model = BaseModel::getInstance(GoodsModel::class);
        if (!empty($_GET[GoodsModel::$title_d])) {
            $_GET[GoodsModel::$title_d] = urldecode($_GET[GoodsModel::$title_d]);
        }
        //设置搜索默认值
        Tool::isSetDefaultValue($_GET, array(
            GoodsModel::$classId_d,
            GoodsModel::$brandId_d,
            GoodsModel::$title_d,
            GoodsModel::$shelves_d,
        ), '');
        $brandList = S('brandList');

        if (empty($brandList)) {
            $brandList = BaseModel::getInstance(BrandModel::class)->getField(BrandModel::$id_d.','.BrandModel::$brandName_d);

            S('brandList', $brandList, 60);
        }

        //获取商品列表
        $classData = BaseModel::getInstance(GoodsClassModel::class)->getClassDataByStatus();
        $where = array($model::$pId_d  => 0);

        Tool::connect('ArrayChildren');

        $bulidSearch = $model->buildSearch($_GET, true, array(GoodsModel::$title_d));

        $where = array_merge($where, $bulidSearch);

		$count = $model->where($where)->count();

		$page_setting = C('PAGE_SETTING');

		$rows = $model->getPageByGoodsData($where);
		//获取商品分类
		$goods_class = M("GoodsClass")->where(['hide_status'=>1])->getField('id,class_name');

		$this->assign("goods_class",$goods_class);

		$this->assign('goodsModel', GoodsModel::class);

		$this->assign('classData', $classData);
		$this->assign('brandList', $brandList);
		$this->assign('rows',$rows['data']);
		$this->assign('page_show',$rows['page']);
		$this->assign('title',C('title'));
		return $this->display();

    }

	/**
	 * 批量编辑商品名称 编辑页面
	 */
	public function goods_more_save()
	{
		if(IS_POST) {
			if($_POST['checkbox']==false)
			{
				$this->redirect('Goods/goods_list');
			}else {
				$where['id'] = array('in', $_POST['checkbox']);
				$save_data = M('goods')->field('id,title,shelves,recommend')->where($where)->select();
			}
		}
			$this->assign('save_data', $save_data);
			$this->display();
	}

	/**
	 * 批量编辑商品名称 添加到数据库 返回商品列表页
	 */
	public function good_save_post()
	{
		foreach($_POST['goods'] as $k=>$v)
		{
			$_POST['goods'][$k]['recommend']=($v['recommend']==false)?'0':$v['recommend'];
			$_POST['goods'][$k]['shelves']=($v['shelves']==false)?'0':$v['shelves'];
			$_POST['goods'][$k]['update_time']=($v['update_time']==false)?time():$v['update_time'];
		}
		$p_id=array();
		$p_title=array();
		//更新父类数据
		foreach($_POST['goods'] as $k=>$v)
		{
			M('goods')->create($v);
			$save_status=M('goods')->save();
			$p_id[]=$v['id'];
			$p_title[]=$v['title'];
		}
		$where['p_id']=array('in',$p_id);
		$where['status']=0;
		$children_goods=M('goods')->field('id,p_id')->where($where)->select();
		if(!$children_goods)
		{
			$this->success('请补全产品规格',U('goods_list'));
			exit;
		}
		foreach($children_goods as $k=>$v)
		{
			$children_goods[$k]['key']=M('SpecGoodsPrice')->field('key')->where('goods_id='.$v['id'])->find()['key'];
		}
		foreach($children_goods as $k=>$v)
		{
			$children_goods[$k]['item']=M('GoodsSpecItem')->field('item')->where(array('id'=>array('in',explode('_',$v['key']))))->select();
			$children_goods[$k]['title']=M('Goods')->field('title')->where('id='.$v['p_id'])->find()['title'];
		}
		foreach($children_goods as $k=>$v)
		{

			foreach($v['item'] as $k1=>$v2)
			{
				$children_goods[$k]['new_item'][]=$v2['item'];
			}
		}
		$save_arr=array();
		foreach($children_goods as $k=>$v)
		{
			$children_goods[$k]['save_title']=$v['title'].' '.implode(' ',$v['new_item']);
		}
		foreach($children_goods as $k=>$v)
		{
			$save_arr[$v['id']]['id']=$v['id'];
			$save_arr[$v['id']]['title']=$v['save_title'];
		}
		//更新所有子类的名称
		foreach($save_arr as $v)
		{
			M('goods')->create($v);
			$save_children_arr=M('goods')->save();
		}

		if($save_status && $save_children_arr)
		{
			$this->success('编辑成功',U('goods_list'));
		}else if($save_status==0)
		{
			$this->success('并未对数据进行修改',U('goods_list'));
		}else
		{
			$this->success('未知错误',U('goods_list'));
		}
	}


	public function checkGoodsTitle(){
		$goods_title = $_GET['goods_title'];
		$result = M("Goods")->where(['title'=>$goods_title])->find();
		if($result){
			$this->ajaxReturn(["msg"=>0]);
		}else{
			$this->ajaxReturn(["msg"=>1]);
		}
	}

	/**
	 * 修改商品
	 * @param int  $id 商品id
	 */
	public function modifyGoods($id)
	{
	    $this->errorNotice($id);
		//回显该条商品的数据
	    $goodsModel = BaseModel::getInstance(GoodsModel::class);

	    $row = $goodsModel->getInfoGoods($id);
	    $this->prompt($row);
	    
	    $classId = $row[GoodsModel::$classId_d];
	    
	    $second  = $row[GoodsModel::$classId_d];
	    
	    $three   = $row[GoodsModel::$classId_d];
	    
		//获取顶级分类
		$cat_ss = $this->getTopClass($classId, 0);
		//二级
		$secondData = $this->getTopClass($second, 1);
		//三级
		$threeData = $this->getTopClass($three, 2);
		
		
		$classData = [
		     $cat_ss[GoodsClassModel::$id_d]      => $cat_ss[GoodsClassModel::$className_d],
		     $secondData[GoodsClassModel::$id_d]  => $secondData[GoodsClassModel::$className_d],
		     $threeData[GoodsClassModel::$id_d]   => $threeData[GoodsClassModel::$className_d],
		];
		$classModel = BaseModel::getInstance(GoodsClassModel::class);
		$extendClassData = $classModel->getTopClass();
		
		$extendClassById = $classModel->getExtendCollection($row[GoodsModel::$extend_d]);
		
		//回显商品详情表
		$row['detail'] = BaseModel::getInstance(GoodsDetailModel::class)
		      ->field(GoodsDetailModel::$detail_d)
		      ->where([GoodsDetailModel::$goodsId_d=>$id])
		      ->find();
		//回显商品相册
		$goodsImages = BaseModel::getInstance(GoodsImagesModel::class)->where([GoodsImagesModel::$goodsId_d => $id, GoodsImagesModel::$isThumb_d=> 0])->select();

		if($row['stock']==0)
		{
			$row['advance_date']=floor(($row['advance_date']-time())/(24*60*60));
		}else{
			$row['advance_date']=($row['advance_date']/(24*60*60));
		}
		//dump($after_date);exit;
		$this->assign('specItemModel', GoodsSpecItemModel::class);
		//回显数据
		$this->assign("goodsImages",$goodsImages);
		$this->assign("row",$row);

		$this->goods_before();

		$this->assign('goodsModel', GoodsModel::class);

		$this->assign("cat_ss",$cat_ss);
        //顶级
        $this->assign('classId', $classId);
        $this->assign('secondData', $secondData);
        $this->assign('threeData', $threeData);
        $this->assign('classData', json_encode($classData));
        $this->assign('extendClassData', ($extendClassData));
        $this->assign('extendMyData', ($extendClassById));
		$this->display();
	}

	/**
	 * 保存商品 【第一步】
	 */
	public function saveGoods()
	{
	    Tool::checkPost($_POST, array('is_numeric' => array('brand_id', 'id'), 'detail', 'extend', 'goods_images', 'sku'), true , array(
	            'class_id', 'brand_id', 'title','description',/*'code','price_market','price_member',*/'stock',/* 'd_integral', */'shelves','recommend','detail'
	    )) ? true : $this->ajaxReturnData($_POST, 0, '参数错误');
	    $this->promptPjax($_POST['detail'], '商品详情错误');
	    
	    $_POST['detail'] = htmlspecialchars($_POST['detail']);
	    $model = BaseModel::getInstance(GoodsModel::class);
        
	    
	    //重组数据 根据规格生成对应名字
        $specItemModel = BaseModel::getInstance(GoodsSpecItemModel::class);
      
        $specItemModel->setTitle($_POST['title']);
      
        $specItemModel->setTitleKey('title');
      
        $_POST['item']  = $specItemModel->getGoodsNameByItem($_POST['item']);
	   
	    $status = $model->saveGoods($_POST);
	    
	    $this->promptPjax($status, $model->getError());
	    
	    $goodsDetail = BaseModel::getInstance(GoodsDetailModel::class);
	   
	    $_POST[GoodsDetailModel::$goodsId_d] = $_POST['id'];
	    unset($_POST['id']);
       
	    $status = $goodsDetail->saveData($_POST, GoodsDetailModel::$goodsId_d);
        
	    $this->promptPjax($status, '保存失败');

	    $this->updateClient(array('url' => U('savePricture')));
	}

	/**
	 *  保存图片
	 */
	public function savePricture()
	{
	    //删除空的
	    $_POST['goods_images'] = Tool::connect('ArrayChildren')->deleteEmptyByArray($_POST['goods_images']);
	  
	    Tool::checkPost($_POST, array('is_numeric'=> array('id'), 'extend', 'class_id'), true, array('goods_images', 'id')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
	    $model = BaseModel::getInstance(GoodsImagesModel::class);
	   
	  
	    $_POST[$model::$picUrl_d]  = $_POST['goods_images'];
	    $_POST[$model::$goodsId_d] = $_POST['id'];
	    
	    $thumbWith = $this->getConfig('thumb_image_width');
	    
	    $thumbHeight = $this->getConfig('thumb_image_height');
	    
	    $model->setImageWidth($thumbWith);
	    
	    $model->setImageHeight($thumbHeight);
	    
	    $status = $model->editPicture($_POST);
	    
	    $this->promptPjax($status, '保存失败');
	   
	    $url =  U('editSpecGoods');
	    
	    $this->updateClient(array(
	            'url' => $url,
	            'insertId' => $_SESSION['insertId']
	    ));
	}

	/**
	 * 规格修改
	 */
	public function editSpecGoods()
	{
	    $_POST['goods_images'] = Tool::connect('ArrayChildren')->deleteEmptyByArray($_POST['goods_images']);
        Tool::checkPost($_POST, array('goods_images', 'extend', 'class_id', 'id', 'goods_id'), true ,array('item')) ? true : $this->ajaxReturnData(null, 0, '参数错误');

        //重组数据 根据规格生成对应名字
        $specItemModel = BaseModel::getInstance(GoodsSpecItemModel::class);

        $specItemModel->setTitle($_POST['title']);
         
        $specItemModel->setTitleKey('title');
        
        $_POST['item']  = $specItemModel->getGoodsNameByItem($_POST['item'], $_POST['title'], 'title');

        $this->promptPjax($_POST['item']);

        //保存到商品表中
        $goodsModel = BaseModel::getInstance(GoodsModel::class);

        $specModel  = BaseModel::getInstance(SpecGoodsPriceModel::class);

        $id         = $goodsModel->updateData($_POST, $specModel);
        
        $this->promptPjax($id !== false, $goodsModel->getError());
        
        $specModel->splitKey = SpecGoodsPriceModel::$goodsId_d;

        Tool::connect('parseString');

        $status = $specModel->saveEdit($_POST['item'], $goodsModel->getIdArray());
      
        $error = $specModel->getError();
        $this->alreadyInDataPjax($error, $error.'已存在');
        
        $this->ajaxReturnData(true);

	}

	/**
	 * 删除商品
	 * @param int $id 商品的id
	 */
	public function removeGoods($id)
	{
		$result = D("Goods")->delGoods($id);
		
		$specGoods = BaseModel::getInstance(SpecGoodsPriceModel::class);
		
		//根据类型 判断 走那一步
		$status = AbstractParse::getInstance($result)->parseDataBaseByUser($specGoods);
		
		$this->promptPjax($status !== false, '删除失败');
		
		$goodsDetailModel = BaseModel::getInstance(GoodsDetailModel::class);
		
		$status = $goodsDetailModel->deleteGoodsById($id);
		
		$this->promptPjax($status !== false, '删除失败');
		//删除商品图片
		$goodsImageModel = BaseModel::getInstance(GoodsImagesModel::class);
		
		$status = $goodsImageModel->deletePicture ($id);
		
		//商品属性等
		
		//商品属性等
		$this->promptPjax($status !== false, '删除失败');
		$this->ajaxReturnData(['url' => U('goods_list')]);
	}
    
	/**
	 * 删除一个商品 
	 */
	
	public function deleteOneGood ($id)
	{
	    $goodsModel = BaseModel::getInstance(GoodsModel::class);
	    
	    $status = $goodsModel->deleteGoodById($id);
	    
	    $this->promptPjax($status !== false, '删除失败');
	    
	    $specGoodsModel = BaseModel::getInstance(SpecGoodsPriceModel::class);
	    
	    $status = $specGoodsModel->deleteSpecById($id);
	    
	    $this->updateClient($status, '处理');
	}
	
	/**
	 *　添加商品页面
	 */
	public function goods_add()
	{
	     $goods_model =BaseModel::getInstance(GoodsModel::class);

		 //回显数据
		 $this->goods_before();

		 $this->goodsModel = GoodsModel::class;

		 $this->display();
	}

     //回显数据
	 public function goods_before()
	 {
// 		 获取商品分类
		 $goodsModel = BaseModel::getInstance(GoodsClassModel::class);

		 $goodsClassList = $goodsModel->getlist();
		 $this->assign('goodsClassList',$goodsClassList);

		 //获取品牌
		 $brandModel = BaseModel::getInstance(BrandModel::class);
		 $brandList = $brandModel->field($brandModel::$id_d.','.$brandModel::$brandName_d)->select();
		 $this->assign('brandList',$brandList);

// 		 获取商品类型数据
		 $goodsTypeList = D("GoodsType")->getList();
		 $this->assign('goodsTypeList',$goodsTypeList);
		 
		 //获取商品属性
		 
		 
	 }


	/**
	 * 商品属性 添加
	 */
	public function goodsAttribute()
	{
	    Tool::checkPost($_GET, array('is_numeric' => array('attribute_id')), true, array('attribute_id')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
	    //获取商品属性
	    $attributeModel = BaseModel::getInstance(GoodsAttributeModel::class);

	    $attribute      = $attributeModel->getAttribute(array(
	        'field' => array($attributeModel::$createTime_d, $attributeModel::$updateTime_d),
	        'where' => array($attributeModel::$status_d => 1, $attributeModel::$goodsClassId_d => $_GET['attribute_id'])
	    ), true);


	    //形成树
	    Tool::connect('Tree');
	    $attribute = Tool::makeTree($attribute, array('parent_key' => $attributeModel::$pId_d));
	    $this->attribute = $attribute;
	    $this->model     = GoodsAttributeModel::class;
	    $this->display();
	}

	/**
	 * 商品属性 添加价格 库存等
	 */
	public function getAddContentByGoodsAttribute()
	{
	    $htmlString = '';

	    if (!empty($_POST['spc'])) {


	        // 获取笛卡尔积
	        $data =  Tool::connect('ArrayChildren')->parseSpecific($_POST['spc']);

    	    $goodsSpc = BaseModel::getInstance(GoodsSpecModel::class);

    	    $id = array();
    	    if (!empty($_POST['goods_id'])) {

    	        Tool::connect('parseString');

    	        $_SESSION['goodsIdArr'] = BaseModel::getInstance(GoodsModel::class)->field(GoodsModel::$id_d)->where(array(GoodsModel::$pId_d => $_POST['goods_id']))->select();
    	    }


    	    //获取规格表
    	    $specData = $goodsSpc->getField($goodsSpc::$id_d.','.$goodsSpc::$name_d);

    	    //获取规格项
    	    $goodsSpcItem     = BaseModel::getInstance(GoodsSpecItemModel::class);

    	    $goodsSpcItemData = $goodsSpcItem->getField($goodsSpcItem::$id_d.','.$goodsSpcItem::$item_d.','.$goodsSpcItem::$specId_d);
    	    //组合所有数据
    	    $specGoodsPrice   = BaseModel::getInstance(SpecGoodsPriceModel::class);

    	    $htmlString       = $specGoodsPrice->getAttributeBuildGoodsInfo($specData, $data, $goodsSpcItemData, $goodsSpcItem);
	    }
	    $this->ajaxReturnData($htmlString);
	}

	/**
	 * 查看商品
	 * @param number $id spu编号
	 * @return
	 */
	public function lookGoods ($id)
	{
	    //检测编号
	    $this->errorNotice($id);

	    $goodsModel = BaseModel::getInstance(GoodsModel::class);

	    Tool::connect('parseString');
	    $goodsData  = $goodsModel->getGoodsDataByParentId($id, BaseModel::getInstance(GoodsClassModel::class));

	    $this->proGoods = $goodsData;

	    $this->goodsModel = GoodsModel::class;

	    $this->goodsClassModel = GoodsClassModel::class;

	    return $this->display();
	}



	/***
	 * {@inheritDoc}
	 * @see \Common\Controller\AuthController::getChildren()
	 */
	public  function getChildren($id = 'goods_class_id')
	{
	    parent::getChildren('class_id');
	}

	/**
	 * 保存商品信息
	 */
	public function addGoods()
	{
	    
	    $validate  = array(
	        'brand_id', 'title','description',/*'code','price_market','price_member',*/'stock',/* 'd_integral' ,*/'shelves','recommend','detail'
	    );
	    
	    Tool::checkPost($_POST, array('is_numeric' => array('brand_id'), 'detail','goods_images', 'goods_id', 'attr_id', 'extend', 'class_name'), true , $validate) ? true : $this->ajaxReturnData(null, 0, '参数错误');

	    $this->promptPjax($_POST['detail'], '商品详情无数据');
	    $_POST['detail'] = htmlspecialchars($_POST['detail']);
	    $model = BaseModel::getInstance(GoodsModel::class);

	    $where =  array($model::$title_d => $_POST[$model::$title_d]);


	    //是否存在
	    $isAlready = $model->getAttribute(array(
	        'field' => array($model::$id_d),
	        'where' => $where
	    ), false, 'find');
	    $this->alreadyInData($isAlready, '已存在【'.$_POST[$model::$title_d].'】');

	    $insertId = $model->addTranstaion($_POST);

	    $this->promptPjax($insertId, '操作失败');

	    $detailModel = BaseModel::getInstance(GoodsDetailModel::class);

	    $_POST[$detailModel::$goodsId_d] = $insertId;
	   
	    $detailModel->setIsCommit(true);
	    $insert = $detailModel->addTranstaion($_POST);
	   
	    $this->promptPjax($insert);
	    $_SESSION['insertId'] = $insertId;
	    $id['insertId'] = $insertId;
	    $id['url']      = U('pictureAlbum');
	    $this->updateClient($id);
	}

	/**
	 * 商品相册
	 */
	public function pictureAlbum()
	{
	   
	    //删除空的
	    Tool::connect('ArrayChildren');
	    
	    $_POST['goods_images'] = Tool::deleteEmptyByArray($_POST['goods_images']);
	    
	    Tool::checkPost($_POST, array('is_numeric'=> array('goods_id'), 'class_name', 'extend'), true, array('goods_images')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
	   
	    $model = BaseModel::getInstance(GoodsImagesModel::class);

	    empty($_SESSION['insertId']) ? $this->ajaxReturnData('数据错误', '请从头添加') : true;
	    
	    
	    $imageSource =  AbstractParse::getInstance($_POST['goods_images'])->actionRun();
	     
	    $this->imageSource[] = $imageSource;
	     
	    $thumbWith = $this->getConfig('thumb_image_width');
	     
	    $thumbHeight = $this->getConfig('thumb_image_height');
	     
	    $thumbImageArray = $this->buildThumbImage(intval($thumbWith), intval($thumbHeight));
	    
	    
	    $_POST[$model::$picUrl_d]  = array_merge($_POST['goods_images'], $thumbImageArray);
	   
	    $_POST[$model::$goodsId_d] = $_SESSION['insertId'];
	    $status = $model->addAll($_POST);
	    $url = empty($status) ? '' : U('specAddByGoods');
	    $this->updateClient(array(
	        'url' => $url,
	        'insertId' => $_SESSION['insertId']
	    ));
	}

	/**
	 * 商品规格添加【一个规格一个产品】
	 *  
        [item] => Array
        (
            [124] => Array
            (
                [price] => 12
                [store_count] => 23
                [sku] => 11
            )
            [125] => Array
            (
                [price] => 22
                [store_count] => 11
                [sku] => 454
            )
        )
	 */
	public function specAddByGoods()
	{
	    //
	      $_POST['goods_images'] = Tool::connect('ArrayChildren')->deleteEmptyByArray($_POST['goods_images']);
	      Tool::checkPost($_POST, array('is_numeric'=> array('goods_id'),'sku', 'extend', 'class_name'), true, array('item')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
          
	      //重组数据 根据规格生成对应名字
	      $specItemModel = BaseModel::getInstance(GoodsSpecItemModel::class);
          
	      $specItemModel->setTitle($_POST['title']);
	      
	      $specItemModel->setTitleKey('title');
	      
	      $_POST['item']  = $specItemModel->getGoodsNameByItem($_POST['item']);
        
	      $this->promptPjax($_POST['item']);

	      $specGoodsModel = BaseModel::getInstance(SpecGoodsPriceModel::class);

	      //先生成商品
	      $goodsModel = BaseModel::getInstance(GoodsModel::class);

	      $insertId =  $goodsModel->addSpecDdataByGoods($_POST, $specGoodsModel);

	      //根据规格 生成 对应数量的商品
	      $this->promptPjax($insertId, '未知错误，请仔细核对在提交');

	      //生成 商品-规格对应
	      $status    = $specGoodsModel->addSpecByGoods($_POST['item'], $insertId);

	      $this->promptPjax($status, $specGoodsModel->getError());
	      
	      $this->updateClient(array(
	        'url' => U('AjaxGetAttribute/addGoodsAttribute'),
	        'insertId' => $_SESSION['insertId']
	    ));

	}

	/**
	 * 删除数据库图片数据
	 */
	public function deleteImageByDb()
	{
	    Tool::checkPost($_GET, (array)null, false, array('filename')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
	    $model = BaseModel::getInstance(GoodsImagesModel::class);

	    $status = $model->deleteManyPicture($_GET['filename']);

	    $this->updateClient(1);
	}

	public function ajaxgoods() {
		$id = I('post.id');
		$info = M('goods_class')->where('fid='.$id)->select();
		if(empty($info)) {
			$this->ajaxReturn(0);
		}
		$this->ajaxReturn($info);
	}


    //删除活动
    public function goods_del(){
    	$where['id'] = $_POST['id'];
    	$m = M('goods');
    	$result = $m->where($where)->delete();
    	if($result){
    		$data['code'] = '1';	//删除成功
    		$this->ajaxReturn($data);
    	}else{
    		$data['code'] = '0';	//删除失败
    		$this->ajaxReturn($data);
    	}
    }

	/**
	 * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
	 */
	public function ajaxGetSpecSelect(){
		$goods_id = $_GET['goods_id'] ? $_GET['goods_id'] : 0;

		$type_id = $_GET['spec_type'];
		$specList = D('GoodsSpec')->where(['type_id'=>$type_id])->select();
		foreach($specList as $k => $v)
			$specList[$k]['spec_item'] = D('GoodsSpecItem')->where("spec_id = ".$v['id'])->order('id')->getField('id,item'); // 获取规格项


		$goodsModel = BaseModel::getInstance(GoodsModel::class);
		Tool::connect('parseString');
		$spcGoods = $goodsModel->innerJoin($goods_id, GoodsModel::$id_d);

		$itemsIds = array();

		if (!empty($spcGoods)) {
		    $itemsId = BaseModel::getInstance(SpecGoodsPriceModel::class)
		    ->where(SpecGoodsPriceModel::$goodsId_d .' in ('.$spcGoods.')')
		    ->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");

		    $itemsIds = explode('_', $itemsId);
		}


		$this->assign('itemsId',$itemsIds);
		$this->assign('specList',$specList);
		$this->display('ajax_spec_select');
	}




	//更新排序
    public function class_sort(){
    	$m = M('goods_class');
    	$str_id = explode(',', substr($_GET['str_id'],1));
    	$str_sort = explode(',', substr($_GET['str_sort'],1));
    	foreach ($str_id as $k=>$v){
    		$data['sort_num'] = $str_sort[$k];
    		$m->where('id='.$v)->save($data);
    	}
    	$this->ajaxReturn(1);
    }

    /**
     * 关键词设置
     */
    public function hotWords()
    {
        //显示关键词列表
        $data = HotWordsModel::getInition()->getAll( array(
            'order' => array('create_time DESC', 'update_time DESC'),
        ), GoodsClassModel::getInitnation()) ;

        $this->data = $data;
        $this->display();
    }

    /**
     * 删除关键词
     */
    public function deleteHotWords()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array('id')
        ), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '参数错误');

        $isSuccess = HotWordsModel::getInition()->where('id="'.$_POST['id'].'"')->delete();
        $status  = !empty($isSuccess) ? 1 : 0;
        $message = !empty($isSuccess) ? '删除成功' : '删除失败';
        $this->ajaxReturnData(null, $status, $message);
    }

    /**
     * 添加关键词
     */
    public function add_hot_words()
    {
        $this->classData = BaseModel::getInstance(GoodsClassModel::class)->getAllClassId(array(
            'where' =>array(
                'hide_status' => 1,
            ),
            'field'=> array(
               'id', 'class_name', 'fid'
            )
        ));
        $this->display();
    }

    /**
     * 返回树形结构
     */

    public function buildTree()
    {
        $this->ajaxReturnData($this->getClass());
    }
    /**
     * 保存关键词
     */
    public function add_save_hotwords()
    {
        \Common\Tool\Tool::checkPost($_POST, array(
            'is_numeric' => array('goods_class_id', 'is_hide')
        ), true, array('goods_class_id', 'hot_words', 'is_hide')) ? true : $this->ajaxReturnData(null, 0, '数据有误，请重新输入');

        if (HotWordsModel::getInition()->isHaveHotWords($_POST))
        {
            $this->ajaxReturnData(null, 0, '该分类已存在该关键词');
        }

        $insert_id = HotWordsModel::getInition()->add($_POST);

        $status    = empty($insert_id) ? 0 : 1;
        $message   = empty($insert_id) ? '添加失败' : '添加成功';

        $this->ajaxReturnData($insert_id, $status, $message);
    }

    /**
     * 编辑
     */
    public function editHotWords()
    {
        \Common\Tool\Tool::checkPost($_GET, array(
            'is_numeric' => array('id')
        ), true, array('id')) ? true : $this->error('灌水机制已打开');

        $data = HotWordsModel::getInition()->find(array(
            'where' => array('id = "'.$_GET['id'].'"'),
            'field' => array('id', 'hot_words', 'goods_class_id', 'is_hide')
        ), GoodsClassModel::getInitnation());
        //获取商品分类
        $this->classData = $this->getClass();
        $this->data = $data;

        $this->display();
    }

    /**
     * 保存编辑
     */
    public function saveHotWords()
    {
        \Common\Tool\Tool::checkPost($_POST, array(
            'is_numeric' => array('goods_class_id', 'is_hide', 'id')
        ), true, array('goods_class_id', 'hot_words', 'is_hide', 'id')) ? true : $this->ajaxReturnData(null, 0, '数据有误，请重新输入');

        $insert_id = HotWordsModel::getInition()->save($_POST);

        $status    = empty($insert_id) ? 0 : 1;
        $message   = empty($insert_id) ? '更新失败' : '更新成功';

        $this->ajaxReturnData($insert_id, $status, $message);
    }


	/**
	 * 是否推荐 或者上架
	 */
	public function isShelves()
	{
	   
	    Tool::checkPost($_POST, array('is_numeric'=> array('id')), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '操作失败');
        
	    $status = BaseModel::getInstance(GoodsModel::class)->saveData($_POST);
	   
	    $this->updateClient($status, '操作');
	}
	/**
	 * 修改其中一个 【是否推荐 或者上架】
	 */
	public function isShelve()
	{
	    Tool::checkPost($_POST, array('is_numeric'=> array('id')), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '操作失败');
	    $status = BaseModel::getInstance(GoodsModel::class)->save($_POST);
	
	    $this->updateClient($status, '操作');
	}
	
	/**
	 * @desc  生成Excel
	 * @param unknown $expTitle
	 * @param unknown $expCellName
	 * @param unknown $expTableData
	 */
	public function exportExcel($expTitle,$expCellName,$expTableData){
		$xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
		$fileName = $expTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
		$cellNum = count($expCellName);
		$dataNum = count($expTableData);
		vendor("PHPExcel.PHPExcel");

		$objPHPExcel = new \PHPExcel();
		$cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

		$objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
		// $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
		for($i=0;$i<$cellNum;$i++){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
		}
		// Miscellaneous glyphs, UTF-8
		for($i=0;$i<$dataNum;$i++){
			for($j=0;$j<$cellNum;$j++){
				$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
			}
		}
		ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
		header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	/**
	 * 全部导出excel
	 * 当前页导出execl
	 *
	 * 通过当前页数（p）来进行判断是全部导出还是当前页导出
	 *     1.如果有p参数，就是当前页导出、
	 *     2.如果没有p参数，就是全部导出
	 */
	public function expGoods(){
		$tj_value = json_decode($_GET['tj_value'],true);
		$cond = [];
	    $tj_value['class_id']?$cond['class_id']=$tj_value['class_id']:false;
	    $tj_value['brand_id']?$cond['brand_id']=$tj_value['brand_id']:false;
	    $tj_value['shelves']?$cond['shelves']=$tj_value['shelves']:false;
	    $tj_value['title']?$cond['title']=['like','%'.$tj_value['title'].'%']:false;
		//获取p参数
		$current_page = $tj_value['p'];
		$cond['p_id'] = 0;
		$xlsName  = "goods";
		$xlsCell  = array(
			array('id','id'),
			array('title','商品名称'),
			array('code','货号'),
			array('class_id','商品分类'),
			array('price_market','市场价'),
			array('price_member','会员价'),
			array('stock','库存'),
			array('shelves','是否上架'),
			array('recommend','是否推荐'),
			array('sort','排序'),
		);
		$goodsClassModel = M("GoodsClass");
		$xlsModel = M('Goods');
		if($current_page){//当前页导出excel
			$page_setting = C('PAGE_SETTING');
			$xlsData  = $xlsModel
				->field('id,title,code,class_id,price_market,price_member,stock,shelves,recommend,sort')
				->where($cond)
				->page($current_page,$page_setting['PAGE_SIZE'])
				->order(['create_time'=>'desc','sort'])
				->select();
		}else{//全部导出excel
			$xlsData  = $xlsModel
				->field('id,title,code,class_id,price_market,price_member,stock,shelves,recommend,sort')
				->where($cond)
				->order(['create_time'=>'desc','sort'])
				->select();
		}
		foreach($xlsData as &$v){
			if($v['shelves'] == 1){
				$v['shelves'] = "是";
			}else{
				$v['shelves'] = "否";
			}
			if($v['recommend'] == 1){
				$v['recommend'] = "是";
			}else{
				$v['recommend'] = "否";
			}
			//用商品分类表里面的class_name来替换class_id
			$v['class_id'] = $goodsClassModel->where(['id'=>$v['class_id']])->getField('class_name');
		}
		unset($v);
		$this->exportExcel($xlsName,$xlsCell,$xlsData);

	}

	/**
	 * 商品批量删除
	 */

	public function ajax_goods_more_deleted()
	{
		$new_array = explode(',', $_POST['formdata']);
		$end_array = array();
		foreach ($new_array as $k => $v) {
			$end_array[$v]['id'] = $v;
		}
		//查找子类及父类图片
		$delete=$this->old_array_change($end_array);
		$this->ajaxReturn(array('delete'=>$delete,'url'=>U('Goods/goods_list')));
	}

	/**
	 * 批量删除方法
	 */
	public function old_array_change($end_array)
	{
		foreach($end_array as $k=>$v)
		{
			$end_array[$k]['children_id']=M('Goods')->field('id')->where(array('p_id'=>$v['id']))->select();
			$end_array[$k]['goods_image']=M('GoodsImages')->field('pic_url')->where(array('goods_id'=>$v['id']))->select();
		}
		//删除父类图片及数据库图片记录，将子类的属性删除
		foreach($end_array as $k=>$v) {
			//父类记录
			M('Goods')->where(array('id'=>$v['id']))->delete();
			//父类图片数据库记录删除
			M('GoodsImages')->where(array('goods_id'=>$v['id']))->delete();
			//父类图片删除
			foreach ($v['goods_image'] as  $v1)
			{
				unlink('.'.$v1['pic_url']);
			}
			//子类属性删除，子类记录
			foreach($v['children_id'] as $v2)
			{
				M('Goods')->where(array('id'=>$v2['id']))->delete();
				M('SpecGoodsPrice')->where(array('goods_id'=>$v2['id']))->delete();
			}
		}
		return true;
	}
}