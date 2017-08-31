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
use Common\Model\BaseModel;
use Common\Model\SystemConfigModel;
use Admin\Model\ConfigChildrenModel;
use Admin\Model\ConfigClassModel;
use Common\Tool\Tool;
use Admin\Model\UserLevelModel;
use Admin\Model\GoodsModel;
use Admin\Model\PromGoodsModel;
use Admin\Model\PromotionGoodsModel;
use Common\Model\PromotionTypeModel;
use Common\TraitClass\SearchTrait;

/**
 * 促销管理 
 */
class PromotionController extends AuthController
{
    use SearchTrait;
    /**
     * 最新促销
     * @var int
     */
    
    /**
     * 促销列表 
     */
    public function index()
    {
        $model = BaseModel::getInstance(PromGoodsModel::class);
        
        Tool::isSetDefaultValue($_POST, array(PromGoodsModel::$name_d => ''));
        
        Tool::connect('ArrayChildren');
        $where = $model->buildSearch($_POST, true);
        
        $data = $model->getClassDataByPage($where);
        
        //传递促销类型表
        $proType     = BaseModel::getInstance(PromotionTypeModel::class);
        
        Tool::connect('parseString');
        
        $data['data'] = $proType->getDataByOtherModel($data['data'], $model::$type_d, array(
            $proType::$id_d, $proType::$promationName_d
        ), $proType::$id_d);
        
        //获取适用用户
        $userLevelModel = BaseModel::getInstance(UserLevelModel::class);
        
        $data['data'] = $userLevelModel->getDataByGroup($data['data'], 
            $userLevelModel::$id_d.','. $userLevelModel::$levelName_d
        , $model::$group_d);
        $this->promotionData = $data;
        
        $this->proGoodsModel = PromGoodsModel::class;
        
        $this->proType       = PromotionTypeModel::class;
        
        $this->display();
    }

    public function gift()
    {
        //如果传值显示模糊搜索数据
        if(IS_POST)
    {
        $gift_list=$this->gift_like_select($_POST['like_data']);
    }else{
            $gift_list=$this->gift_list();
        }
        $this->assign('gift_list',$gift_list);
        $this->display();
    }

    /**
     * 赠品列表模糊查询(数组的模糊查询)
     */
    public function gift_like_select($like_data)
    {
        $like_array_select=array();
        foreach($this->gift_list() as $k=>$v)
        {
            if(strstr($v['expression'],$like_data)!==false)
            {
                array_push($like_array_select,$v);
            }
        }
       return $like_array_select;
    }

    /**
     * 获取赠品列表数据
     */
    public function gift_list()
    {
        $gift_list=M('CommodityGift')->field('id,goods_id,type,expression,group,start_time,end_time')->where('status=1')->order('id desc')->select();
        $user_lever=M('UserLevel')->where('status=1')->select();
        $repalce_name=array();
        $repalce_id=array();
        foreach($user_lever as $v)
        {
            $repalce_name[]=($v['level_name']);
            $repalce_id[]=($v['id']);
        }
        foreach($gift_list as $k=>$v)
        {
            $gift_list[$k]['type_name']=($v['type']==1)?'商品赠品':'满赠';
            $gift_list[$k]['expression']=($v['expression']==0)?mb_substr(M('goods')->where('id='.$v['goods_id'])->find()['title'],0,19,'utf-8'):$v['expression'];
            $gift_list[$k]['sum']=$v['group'];
            for($i=0;$i<count($repalce_name);$i++)
            {
                $haystack = '-_-!' . $gift_list[$k]['sum'];
                if(strpos($haystack,$repalce_id[$i]))
                {
                    $gift_list[$k]['sum']=str_replace($repalce_id[$i],$repalce_name[$i],$gift_list[$k]['sum']);
                }
            }
        }
        foreach($gift_list as $k=>$v)
        {
            $gift_list[$k]['sum']=preg_replace('|[0-9a-zA-Z,]+|','',$v['sum']);
        }
        foreach($gift_list as $k=>$v)
        {
            $gift_list[$k]['sum']=($v['sum']=='')?'未添加范围':$v['sum'];
        }
        return $gift_list;
    }

    public function addGift()
    {
        $userLevelData  = self::getUserLevel();

        $this->getProType();

        $this->userLevel = $userLevelData;

        BaseModel::getInstance(PromGoodsModel::class);

        $this->proGoodsModel = PromGoodsModel::class;
        $this->display();
    }
    /**
     * 查看促销商品 
     */
    public function lookGoods($id)
    {
       $this->errorNotice($id);
       
       //获取促销商品模型
       $proGoodsModel = BaseModel::getInstance(PromotionGoodsModel::class);
       
       $proGoodsData  = $proGoodsModel->getDataByPage(array(
           'field' => array(
               $proGoodsModel::$promId_d,
               $proGoodsModel::$goodsId_d
           ),
           'where' => array(
               $proGoodsModel::$promId_d => $id
           )
       ));
      
       
       $goodsModel = BaseModel::getInstance(GoodsModel::class);
       
       Tool::connect('parseString');
       $proGoodsData['data']  = $goodsModel->getDataByOtherModel($proGoodsData['data'], $proGoodsModel::$goodsId_d, array(
           GoodsModel::$id_d,
           GoodsModel::$title_d,
           GoodsModel::$priceMember_d,
           GoodsModel::$stock_d
       ),  GoodsModel::$id_d);
      
       $this->proGoods = $proGoodsData;
       
       $this->goodsModel = GoodsModel::class;
      
       return $this->display();
       
    }

    /**
     * 查看促销赠品
     */
    public function lookGifts()
    {
        $this->errorNotice($_GET['id']);
        if($_GET['type']==0)//为满赠时
        {
            $row_gift_data=M('CommodityGift')->where('id='.$_GET['id'])->find()['goods_id'];
            $string_arr['goods_id']=array('in',explode(',',$row_gift_data));
            $string_arr['parent_id']=0;
            $string_arr['gift_id']=$_GET['id'];
            $look_gifts_data=M('gifts')->where($string_arr)->select();
            foreach($look_gifts_data as $k =>$v)
            {
                $look_gifts_data[$k]['title']=M('goods')->where('id='.$v['goods_id'])->find()['title'];
            }
        }else if($_GET['type']==1)//为单品赠品时
        {
            $row_gift_data=M('CommodityGift')->where('id='.$_GET['id'])->find()['goods_id'];
            $look_gifts_data=M('gifts')->where('parent_id='.$row_gift_data)->select();
            foreach($look_gifts_data as $k=>$v)
            {
                $look_gifts_data[$k]['title']=M('goods')->where('id='.$v['goods_id'])->find()['title'];
            }
        }
        $this->assign('look_gifts_data',$look_gifts_data);
        return $this->display();
    }
    /**
     * 添加促销产品 
     
    public function addHtml()
    {
        //获取促销类型配置
        
        $systemConfigModel = BaseModel::getInstance(SystemConfigModel::class);
        
        //获取促销配置父类编号
        $data              = $systemConfigModel->getDataByKey('promotiont_set');
        
        $classModel        = BaseModel::getInstance(ConfigClassModel::class);
        
        //获取促销选项
        $classData         = $classModel->getClassDataById($data);
        
        $configClassModel  = BaseModel::getInstance(ConfigChildrenModel::class);
        
        //获取 对应的属性name名称
        $childrenData     = $configClassModel->getDataById($classData);
        
        $data = Tool::connect('Mosaic')->parseToArray($data);
        
        
        //使用用户范围【获取用户等级】
        $userLevelModel = BaseModel::getInstance(UserLevelModel::class);
        
        $userLevelData  = $userLevelModel->getField($userLevelModel::$id_d.','.$userLevelModel::$levelName_d);
        
        
        $this->userLevel = $userLevelData;
        
        $this->classData   = $classData;
        
        $this->childrenData = json_encode($childrenData);
        
        $this->data         = json_encode($data);
        
        $this->display();
    }
    */
    
    /**
     * 编辑页面 
     */
    public function editHtml($id)
    {
        $this->errorNotice($id);
        
        $model = BaseModel::getInstance(PromGoodsModel::class);
        
        $data  = $model->getAttribute(array(
            'field' => array(PromGoodsModel::$createTime_d, PromGoodsModel::$updateTime_d),
            'where' => array(
                $model::$id_d => $id
            )
        ), true, 'find');
        
        $this->prompt($data);
        
        $userLevelData  = self::getUserLevel();
        
        $this->getProType();
      
        //获取促销商品
        $promotionGoodsModel = BaseModel::getInstance(PromotionGoodsModel::class);
        
        $promotionGoodsId  = $promotionGoodsModel->getAttribute(array(
            'field' => array(
                PromotionGoodsModel::$goodsId_d
            ),
            'where' => array(
                PromotionGoodsModel::$promId_d => $data[PromGoodsModel::$id_d]
            )
        ));
        
        
        $_SESSION['GOODS_ID_VALIDATE'] = Tool::connect('ArrayChildren', $promotionGoodsId)->betchArray(PromotionGoodsModel::$goodsId_d);//用于编辑验证
      
        Tool::connect('parseString');
        
        $goodsData = BaseModel::getInstance(GoodsModel::class)->getDataByOtherModel($promotionGoodsId, PromotionGoodsModel::$goodsId_d,array(
               GoodsModel::$id_d,
               GoodsModel::$title_d,
               GoodsModel::$priceMember_d,
               GoodsModel::$stock_d
            ),  GoodsModel::$id_d);
      
        $this->userLevel = $userLevelData;
        
        $this->data = $data;
        
        $this->model = PromGoodsModel::class;
        
        $this->proGoodsModel = PromotionGoodsModel::class;
        
        $this->goodsModel = GoodsModel::class;
        
        $this->goodsData = $goodsData;
        
        return $this->display();
    }

    /**
     * 编辑赠品页面
     */
    public function editGift()
    {
       $row_data=M('CommodityGift')->where('id='.$_GET['id'])->find();
        $row_data['type_status']=($row_data['type']==0)?'<option value="0">满赠</option><option value="1">单品送赠品</option>':'<option value="1">单品送赠品</option><option value="0">满赠</option>';
        $row_data['expression']=($row_data['type']==0)?$row_data['expression']:0;
       if($row_data['type']==0)
       {
           $row_gift_data=$row_data['goods_id'];
           $string_arr['goods_id']=array('in',explode(',',$row_gift_data));
           $string_arr['parent_id']=0;
           $string_arr['gift_id']=$_GET['id'];
           $look_gifts_data=M('gifts')->where($string_arr)->select();
           foreach($look_gifts_data as $k =>$v)
           {
               $look_gifts_data[$k]['title']=M('goods')->where('id='.$v['goods_id'])->find()['title'];
           }
       }else if($row_data['type']==1)//为单品赠品时
       {
           $row_gift_data=M('CommodityGift')->where('id='.$_GET['id'])->find()['goods_id'];
           $string_arr['parent_id']=$row_gift_data;
           $string_arr['gift_id']=$_GET['id'];
           $look_gifts_data=M('gifts')->where($string_arr)->select();
           foreach($look_gifts_data as $k=>$v)
           {
               $look_gifts_data[$k]['title']=M('goods')->where('id='.$v['goods_id'])->find()['title'];
           }
           $goods_data=M('goods')->where('id='.$row_gift_data)->find();
       }
        $gift_html='';
        $user_level=M('UserLevel')->where('status=1')->select();
        $user_level_html='';
        $string_status='--!'.$row_data['group'];
        foreach($user_level as $k=>$v)
        {
            if(strpos($string_status,$v['id']))
            {
                $user_level[$k]['checked_status']='checked="checked"';
            }
        }
        foreach($user_level as $v)
        {
            $user_level_html .='<input type="checkbox" name="group[]" '.$v['checked_status'].' value="'.$v['id'].'">'.$v['level_name'].'';
        }
        if($look_gifts_data) {
            foreach ($look_gifts_data as $k => $v) {
                $gift_html .= '<tr><input type="hidden" name="new_save[' . $v['id'] . '][id]" value="' . $v['id'] . '"><input type="hidden" name="new_save[' . $v['id'] . '][goods_id]" value="' . $v['goods_id'] . '"><td class="text-left">' . $v['title'] . '</td><td class="text-left"><input type="text" name="new_save[' . $v['id'] . '][gift_number]" value="' . $v['gift_number'] . '"></td><td class="text-left"><input type="text" name="new_save[' . $v['id'] . '][gift_stock]" value="' . $v['gift_stock'] . '"></td><td><a href="javascript:void(0)" class="ajax_deleted" value="'.$v['id'].'">删除</a></td></tr>';
            }
        }
        if($goods_data){
            $good_html='<tr><input type="hidden" name="goods_id[]" value="'.$goods_data['id'].'"><td class="text-left">'.$goods_data['title'].'</td><td class="text-left">'.$goods_data['price_market'].'</td><td class="text-left">'.$goods_data['stock'].'</td><td><a href="javascript:void(0)" onclick="javascript:$(this).parent().parent().remove();">删除</a></td></tr>';
        }
        $this->assign('good_html',$good_html);
        $this->assign('user_level_html',$user_level_html);
        $this->assign('gift_html',$gift_html);
        $this->assign('row_data',$row_data);
        $this->display();
    }

    /**
     * 编辑时删除按钮：ajax删除gift表中的记录
     */
    public function ajaxDeleted()
    {
        $deleted_status=M('gifts')->where('id='.$_POST['id'])->delete();
        $this->ajaxReturn(array('data'=>$deleted_status));
    }

    /**
     * 编辑赠品数据到数据库
     */
    public function saveGift()
    {
        $giftModel = M('CommodityGift');
        if($_POST['type']==1) {
            $giftData['description'] =$_POST['description'];
            $giftData['end_time'] = strtotime($_POST['end_time']);
            $giftData['start_time'] = strtotime($_POST['start_time']);
            $giftData['group'] = implode(',',$_POST['group']);
            $giftData['type'] = $_POST['type'];
            $giftData['expression'] = 0;
            $giftData['goods_id'] = $_POST['goods_id'][0];
            $giftData['create_time'] = time();
            $giftData['status'] = 1;
            $add_status = $giftModel->where('id='.$_POST['gift_id'])->save($giftData);
            if (!$add_status) {
                $this->promptPjax($giftData, '保存1');
            } else {
                if($_POST['new_save']){
                    foreach($_POST['new_save'] as $k=>$v)
                    {
                        $_POST['new_save'][$k]['parent_id'] =($v['parent_id']==false)?$giftData['goods_id']:false;
                        $_POST['new_save'][$k]['gift_id'] =($v['gift_id']==false)?$_POST['gift_id']:false;
                    }
                    foreach($_POST['new_save'] as $k=>$v)
                    {
                        M('gifts')->create($v);
                        $save_status= M('gifts')->save();
                    }
                    if (!$save_status) {

                    }
                }
                if($_POST['gift'])
                {
                    foreach ($_POST['gift'] as $k => $v) {
                        $_POST['gift'][$k]['parent_id'] = ($v['parent_id'] == false) ? $_POST['goods_id'][0] : $v['parent_id'];
                        $_POST['gift'][$k]['gift_id'] = ($v['gift_id'] == false) ? $_POST['gift_id'] : $v['gift_id'];
                    }
                    if ($add_all_status = M('gifts')->addAll($_POST['gift'])) {
                        $this->updateClient(array('url' => U('gift')), '编辑');
                    }
                }
            }
        }else if($_POST['type']==0){
            $giftData['description'] = $_POST['description'];
            $giftData['end_time'] = strtotime($_POST['end_time']);
            $giftData['start_time'] = strtotime($_POST['start_time']);
            $giftData['type'] = $_POST['type'];
            $giftData['expression'] =$_POST['expression'];
            $giftData['group'] = implode(',',$_POST['group']);
            $giftData['create_time'] = time();
            $giftData['status'] = 1;
            $goods_id=array();
            if($_POST['new_save']){
                foreach($_POST['new_save'] as $k=>$v)
                {
                    $goods_id[]=$v['goods_id'];
                }
                $giftData['goods_id'] = implode(',',$goods_id);
            }
            if($_POST['gift'] && !$_POST['new_save']){
                foreach($_POST['gift'] as $k=>$v)
                {
                    $goods_id[]=$v['goods_id'];
                }
                $giftData['goods_id'] = implode(',',$goods_id);
            }
             if($_POST['gift'] && $_POST['new_save']){
                 $new_goods_id=array();
                 $old_goods_id=array();
                foreach($_POST['gift'] as $k=>$v)
                {
                    $new_goods_id[]=$v['goods_id'];
                }
                foreach($_POST['new_save'] as $k=>$v)
                {
                    $old_goods_id[]=$v['goods_id'];
                }
                $after_good_id=array_merge($old_goods_id,$new_goods_id);
                $giftData['goods_id'] = implode(',',$after_good_id);
            }
            //以上为类型为满赠时更新CommodityGift表中的数据
            if($giftModel->where('id='.$_POST['gift_id'])->save($giftData))
            {
                if($_POST['new_save']){
                    foreach($_POST['new_save'] as $k=>$v)
                    {
                        $_POST['new_save'][$k]['parent_id'] =0;
                        $_POST['new_save'][$k]['gift_id'] =$_POST['gift_id'];
                    }
                    foreach($_POST['new_save'] as $k=>$v)
                    {
                        M('gifts')->create($v);
                        $save_status= M('gifts')->save();
                    }
                }
                if($_POST['gift'])
                {
                    foreach ($_POST['gift'] as $k => $v) {
                        $_POST['gift'][$k]['parent_id'] = 0;
                        $_POST['gift'][$k]['gift_id'] =$_POST['gift_id'];
                    }
                    if ($add_all_status = M('gifts')->addAll($_POST['gift'])) {
                        $this->updateClient(array('url' => U('gift')), '编辑');
                    }
                }
            }
            else {
                $this->promptPjax($giftData, '保存失败');
            }
        }
    }
    
    /**
     * 添加内容页面 
     */
    public function addHtml()
    {
        //使用用户范围【获取用户等级】
        
        $userLevelData  = self::getUserLevel();
        
        $this->getProType();
        
        $this->userLevel = $userLevelData;
        
        BaseModel::getInstance(PromGoodsModel::class);
        
        $this->proGoodsModel = PromGoodsModel::class;
        
        $this->display();
    }
    
    /**
     * 获取用户等级数据 
     */
    private static function getUserLevel() {
        
        //使用用户范围【获取用户等级】
        $userLevelModel = BaseModel::getInstance(UserLevelModel::class);
        
        $userLevelData  = $userLevelModel->getField($userLevelModel::$id_d.','.$userLevelModel::$levelName_d);
        
        return $userLevelData;
    }
    
    
    /**
     * 添加促销商品数据 
     */
    public function addProData()
    {
        Tool::checkPost($_POST, [
            'is_numeric' => ['expression', 'type']
        ], true, ['expression', 'type', 'name', 'group', 'goods_id']) ? true : $this->ajaxReturnData(null, 0, '操作失败');
        
        
        $model = BaseModel::getInstance(PromGoodsModel::class);
        
        
        //是否存在
        $isExits = $model->getAttribute(array(
            'field' => array($model::$id_d),
            'where' => array($model::$name_d => $_POST['name'])
        ));
      
        $this->alreadyInDataPjax($isExits);
        
        
        $status = $model->addProGoods($_POST);
        
        $this->promptPjax($status, $model->getError());
        
        //批量更新商品状态
        $status = BaseModel::getInstance(GoodsModel::class)->setGoodsStatus($_POST['goods_id']);
        
        $this->promptPjax($status, $model->getError());
        
        //添加促销商品对应关系表
        $proModel = BaseModel::getInstance(PromotionGoodsModel::class);
        
        $insertId = $proModel->addGoodsByPromotionId($_POST, intval($status));
        
        $this->promptPjax($insertId, '添加失败');
        
        $this->updateClient(array('url' => U('index')), '操作');
        
    }

    /**
     * 添加赠品数据
     */
    public function addGiftData()
    {
        /*$model = BaseModel::getInstance(PromGoodsModel::class);

        //是否存在
        $isExits = $model->getAttribute(array(
            'field' => array($model::$id_d),
            'where' => array($model::$name_d => $_POST['name'])
        ));

        $this->alreadyInDataPjax($isExits);
        */
        $giftModel = M('CommodityGift');
        if($_POST['type']==1) {
            $giftData['description'] =$_POST['description'];
            $giftData['end_time'] = strtotime($_POST['end_time']);
            $giftData['start_time'] = strtotime($_POST['start_time']);
            $giftData['group'] = implode(',',$_POST['group']);
            $giftData['type'] = $_POST['type'];
            $giftData['expression'] = ($_POST['expression']==false)?0:$_POST['expression'];
            $giftData['goods_id'] = $_POST['goods_id'][0];
            $giftData['create_time'] = time();
            $giftData['status'] = 1;
            $add_status = $giftModel->add($giftData);

            if (!$add_status) {
                $this->promptPjax($giftData, '保存失败');
            } else {
                $gift_data=$giftModel->where('status=1')->order('id desc')->limit(1)->find();
                foreach ($_POST['gift'] as $k => $v) {
                    $_POST[$k] = $v;
                    $_POST['gift'][$k]['parent_id'] = ($v['parent_id'] == false) ? $_POST['goods_id'][0] : $v['parent_id'];
                    $_POST['gift'][$k]['gift_id'] = ($v['gift_id'] == false) ? $gift_data['id'] : $v['gift_id'];
                }
                if ($add_all_status = M('gifts')->addAll($_POST['gift'])) {
                    $this->updateClient(array('url' => U('gift')), '操作');
                } else {
                    $this->ajaxReturnData($add_all_status, 0, '保存失败');
                }
            }
        }else if($_POST['type']==0){
            $giftData['description'] = $_POST['description'];
            $giftData['end_time'] = strtotime($_POST['end_time']);
            $giftData['start_time'] = strtotime($_POST['start_time']);
            $giftData['type'] = $_POST['type'];
            $giftData['expression'] = $_POST['expression'];
            $giftData['group'] = implode(',',$_POST['group']);
            $giftData['create_time'] = time();
            $giftData['status'] = 1;
            $goods_id=array();
            foreach($_POST['gift'] as $k=>$v)
            {
                $goods_id[]=$v['goods_id'];
            }
            $giftData['goods_id'] = implode(',',$goods_id);
            if($giftModel->add($giftData))
            {
                $gift_data=$giftModel->where('status=1')->order('id desc')->limit(1)->find();
                foreach ($_POST['gift'] as $k => $v) {
                    $_POST[$k] = $v;
                    $_POST['gift'][$k]['parent_id'] = ($v['parent_id'] == false) ? 0 : $v['parent_id'];
                    $_POST['gift'][$k]['gift_id'] = ($v['gift_id'] == false) ? $gift_data['id'] : $v['parent_id'];
                }
                if ($add_all_status = M('gifts')->addAll($_POST['gift'])) {
                    $this->updateClient(array('url' => U('gift')), '操作');
                } else {
                    $this->promptPjax($add_all_status, 0, '保存失败');
                }
            }
            else {
                $this->promptPjax($giftData, '保存失败');
            }
        }

        //$status = $model->addProGoods($string);

      /*  $this->promptPjax($status, '保存失败');

        //添加促销商品对应关系表

        $proModel = BaseModel::getInstance(PromotionGoodsModel::class);

        $insertId = $proModel->addGoodsByPromotionId($_POST, intval($status));

        $this->promptPjax($insertId, '保存失败');

        $this->updateClient(array('url' => U('index')), '操作');*/

    }
    /**
     * @desc 编辑促销商品 
     */
    public function editPtomotion()
    {
      
        Tool::checkPost($_POST, [
            'is_numeric' => ['expression', 'type', 'id']
        ], true, ['expression', 'type', 'goods_id', 'name', 'group', 'id']) ? true : $this->ajaxReturnData(null, 0, '操作失败');
        
      
        $proModel = BaseModel::getInstance(PromGoodsModel::class);
        
        $status   = $proModel->addProGoods($_POST, 'save');
        
        $this->promptPjax($status, $proModel->getError());
        
        //验证商品状态 $_SESSION['GOODS_ID_VALIDATE'] 与 $_POST['goods_id'] 之间验证
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        $goodsModel->setArrayData($_SESSION['GOODS_ID_VALIDATE']);
       
        $status = BaseModel::getInstance(GoodsModel::class)->validateGoodsStatus($_POST['goods_id']);
        
        $this->promptPjax($status, $proModel->getError());
        
        $proGoodsModel = BaseModel::getInstance(PromotionGoodsModel::class);
        
        $proStatus     = $proGoodsModel->savePost($_POST, 'id');
        
        $this->promptPjax($proStatus, '保存失败');
        
        $this->updateClient(array('url' => U('index')), '操作');
    }
    
    /**
     * 删除促销活动 
     */
    public function deletePro($id)
    {
        $this->errorNotice($id);
        
        $status = BaseModel::getInstance(PromGoodsModel::class)->deletePro($id);
        
        $status = BaseModel::getInstance(PromotionGoodsModel::class)->deleteProId($id);
        
        $this->updateClient($status, '操作');
    }

    //删除商品促销(没有弹出窗)
    public function deleteGift($id)
    {
        $this->errorNotice($id);
        $deleted['status']=0;
        $delete_status=M('CommodityGift')->where('id='.$id)->save($deleted);
        if($delete_status){
            $this->updateClient($delete_status, '操作');
        }
    }
}