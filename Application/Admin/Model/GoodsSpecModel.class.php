<?php

// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>\n
// +----------------------------------------------------------------------


namespace Admin\Model;


use Think\Model;
use Think\Page;
use Common\Model\BaseModel;

/**
 * 商品规格
 * @author 王强
 * @version 1.0.0
 */
class GoodsSpecModel extends BaseModel
{
    protected $patchValidate = true;
    protected $_validate = [['name','require','商品类型不能为空']];
    
    private static $obj;

	public static $id_d;	//主键编号

	public static $typeId_d;	//商品类型id

	public static $name_d;	//规格名称

	public static $sort_d;	//排序

	public static $status_d;	//状态显示：1显示 0 不显示  默认显示

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间


    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    /**
     * 添加前操作
     */
    protected function _before_insert(&$data,$options)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        return $data;
    }
    //更新前操作
    protected function _before_update(&$data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$name_d, $data[static::$name_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$name_d].'】';
            return false;
        }
        $data['update_time'] = time();
        return $data;
    }

    //获取分类结果
    public function getPageResult(){
        $count = $this->where(['status'=>1])->count();
        //获取分页配置
        $page_setting = C('PAGE_SETTING');
        $page = new Page($count, $page_setting['PAGE_SIZE']);
        $page_show = $page->show();
        $rows = $this->where(['status'=>1]) ->limit($page->firstRow.','.$page->listRows)->select();
        $goods_spec_item_model = D("GoodsSpecItem");
        foreach($rows as $k => $v)
        {       // 获取规格项
            $arr = $goods_spec_item_model->getSpecItem($v['id']);
            $rows[$k]['spec_item'] = implode(' , ', $arr);
        }
        return compact(['rows','page_show']);
    }

    /**
     * 添加规格和规格项
     * @param array $newdata 接收前台的数据
     * @return bool
     */
    public function addSpec($newdata){
        $this->startTrans();
        //保存商品规格表
      
        if(($spec_id = $this->add($newdata))===false){
            $this->error = '规格基本信息保存失败';
            $this->rollback();
            return false;
        }
        //保存到规格项表
        $spec_item_model = M("GoodsSpecItem");
        $post_items = explode(PHP_EOL, $newdata['items']);
        $post_items = $this->filterSpecChar($post_items);
        $post_items = array_unique($post_items);
        $arr = [];
        foreach($post_items as $key => $val)
        {
            $arr[] = [
                'spec_id'=>$spec_id,
                'item'=>$val,
            ];
        }
        if(!empty($arr)){
            if($spec_item_model->addAll($arr) ===false){
                $this->error = "保存规格选项失败";
                $this->rollback();
                return false;
            }
        }
        return $this->commit();
    }
    
    /**
     * 修改商品规格和商品规格选项
     * @param array $newdata 前端传过来的数据
     * @return bool
     */
    public function saveSpec($newdata){
        $this->startTrans();
        //修改商品规格表
        if($this->save($newdata)===false){
            $this->error = "修改商品规格失败";
            $this->rollback();
            return false;
        }

        //修改到规格选项表
        $goods_spec_item_model = M("GoodsSpecItem");
        $spec_id = $newdata['id'];
        $post_items = explode(PHP_EOL, $newdata['items']);
        $post_items = array_unique($post_items);

        //前端传过来的规格选项
        $post_items = $this->filterSpecChar($post_items);
        //数据库中存在的规格选项
        $already_rs = $goods_spec_item_model->where(['spec_id'=>$spec_id])->getField("id,item");
        foreach($post_items as $k=>$v){
            if(in_array($v,$already_rs)){
                $alr_exist[] = $v;
            }else{
                $new_add[] = $v;
            }

        }
        //如果为空时，删除数据库中存在的spec_id
        if(empty($post_items)){
            if(($goods_spec_item_model->where(['spec_id'=>$spec_id])->delete())===false){
                $this->error = "删除多余规格项失败";
                $this->rollback();
                return false;
            }
        }
        //如果存在的数据，数据库不表，多余的删除
        if($alr_exist){
            $arr['spec_id']=$spec_id;
            $arr['item']=[
                ['not in',$alr_exist]
            ];
            if(($goods_spec_item_model->where($arr)->delete())===false){
                $this->error = "删除多余规格项失败";
                $this->rollback();
                return false;
            }
        }



        if($new_add){
           foreach($new_add as $val1){
               $arr1[]=[
                   'spec_id'=>$spec_id,
                   'item'=>$val1
               ];
           }
        }

        //新的规格选项，添加到数据库
        if(!empty($arr1)){
            if($goods_spec_item_model->addAll($arr1) ===false){
                $this->error = "保存规格选项失败";
                $this->rollback();
                return false;
            }
        }
        return $this->commit();


    }

    //过滤特殊的字符
    public function filterSpecChar($post_items){
        foreach ($post_items as $key => $val)  // 去除空格
        {
            $val = str_replace('_', '', $val); // 替换特殊字符
            $val = str_replace('@', '', $val); // 替换特殊字符

            $val = trim($val);
            if(empty($val))
                unset($post_items[$key]);
            else
                $post_items[$key] = $val;
        }
        return $post_items;
    }

    /**
     * 删除商品规格表和删除商品规格项表
     * @param int  $id
     * @return bool
     */
    public function deleteSpec($id){
        $this->startTrans();
        //删除文章基本信息表
        if($this->delete($id)===false){
            $this->rollback();
            return false;
        }
        //删除文章内容表
        $result = M("GoodsSpecItem")->where(['spec_id'=>$id])->delete();
        if($result === false){
            $this->rollback();
            return false;
        }
        return $this->commit();
    }

}