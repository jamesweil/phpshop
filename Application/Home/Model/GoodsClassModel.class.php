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
use Common\Model\BaseModel;
use Common\Tool\Tool;

/**
 *
 * @author Administrator
 */
class GoodsClassModel extends BaseModel
{

    private static $obj;

    public static $id_d;

    public static $className_d;

    public static $createTime_d;

    public static $sortNum_d;

    public static $updateTime_d;

    public static $hideStatus_d;

    public static $picUrl_d;

    public static $fid_d;

    public static $type_d;

    public static $shoutui_d;

    public static $isShow_nav_d;

    public static $description_d;

    public static $cssClass_d;

    public static $hotSingle_d;

    public static $isPrinting_d;

    public static $isHardware_d;

    private  $classData = [];


    public static function getInitnation()
    {
        $class = __CLASS__;
        return ! (self::$obj instanceof $class) ? self::$obj = new self() : self::$obj;
    }

    /**
     * @param multitype: $classData
     */
    public function setClassData($classData)
    {
        $this->classData = $classData;
    }
    
    /**
     * 获取全部子集分类
     *
     * @param array $where
     *            查询条件
     * @param array $field
     *            查询的字段
     * @return string
     */
    public function getChildren(array $where = null, array $field = null)
    {
        // 根据地区编号 查询 该地区的所有信息
        $video_data = parent::select(array(
            'where' => $where,
            'field' => $field
        ));
        $pk = $this->getPk();
        foreach ($video_data as $key => &$value) {
            if (! empty($value[$pk])) {
                $data .= ',' . $value[$pk];
                $child = $this->getChildren(array(
                    'fid' => $value[$pk]
                ), $field);
                if (! empty($child)) {
                    foreach ($child as $key_value => $value_key) {
                        if (! empty($value_key[$pk])) {
                            $data .= ',' . $value_key[$pk];
                        }
                    }
                }
                unset($value, $child);
            }
        }
        return ! empty($data) ? substr($data, 1) : null;
    }

    public function getProductClass(array $options = array())
    {
        if (! is_array($options) || empty($options)) {
            return null;
        }
        
        $resul_class = parent::select($options);
        
        if (! empty($resul_class)) {
            foreach ($resul_class as $k => &$v) {
                $where_sub['fid'] = $v['id'];
                $where_sub['hide_status'] = 0;
                $v['class_sub'] = parent::select(array(
                    'where' => $where_sub
                ));
            }
        }
        return $resul_class;
    }

    /**
     * 获取父及编号
     */
    public function isSameLevel($id = null)
    {
        if (empty($id)) {
            return null;
        }
        
        // 查询我的上级
        $topId = $this->where('id="' . $id . '"')->getField('fid');
        if ($topId != 0) {
            return str_replace('0,', null, $this->isSameLevel($topId) . ',' . $topId);
        } else {
            return $topId;
        }
    }

    /**
     * 查询顶级分类 和当前子分类的数据
     *
     * @param array $options
     *            查询参数
     * @param int $id
     *            分类编号
     * @return array
     */
    public function classTop(array $options, $id)
    {
        if (empty($options) || ! is_array($options)) {
            return array();
        }
        // 顶级分类
        $data = parent::select($options);
        
        $parentId = $this->where('id="' . intval($id) . '"')->getField('fid');
        
        $children = array();
        
        if (! empty($parentId)) {
            $children = parent::select(array(
                'where' => array(
                    'fid' => $parentId,
                    'hide_status' => 0,
                    'type' => 1
                ),
                'field' => array(
                    'id',
                    'class_name'
                )
            ));
        }
        
        // 再次查找 子类(根据父类查找子类)【只查一级，如果是多级 ，请调用getChildren】
        if (empty($children)) {
            $children = parent::select(array(
                'where' => array(
                    'fid' => $id,
                    'hide_status' => 0,
                    'type' => 1
                ),
                'field' => array(
                    'id',
                    'class_name'
                )
            ));
        }
        
        return array(
            'pData' => $data,
            'children' => $children
        );
    }

    public function getChildrens(array $options)
    {
        if (empty($options)) {
            return array();
        }
        return parent::select($options);
    }

    /**
     * 获取所有数据
     *
     * @return mixed
     */
    public function getList()
    {
        $parentField = self::$id_d . ',' . self::$className_d . ',' . self::$fid_d . ',' . self::$cssClass_d;
        
        $parent = $this->field($parentField)
            ->where(self::$shoutui_d . '=1 and ' . self::$isShow_nav_d . ' =0')
            ->select();
        
        return $parent;
    }

    /**
     * 获取下级分类数据
     *
     * @param array $data            
     * @return array
     */
    private function getDataByIds(array $data)
    {
        $parentField = self::$id_d . ',' . self::$className_d . ',' . self::$fid_d . ',' . self::$cssClass_d;
        
        $idString = Tool::characterJoin($data, self::$id_d);
        
        $second = $this->field($parentField)
            ->where(self::$fid_d . ' in (' . $idString . ')')
            ->order(self::$sortNum_d . self::DESC)
            ->select();
        
        return $second;
    }

    /**
     * 获取推荐的父级分类
     */
    public function getRecommendParentClass()
    {
        $parentField = self::$id_d . ',' . self::$className_d . ',' . self::$fid_d . ',' . self::$cssClass_d . ',' . self::$picUrl_d;
        
        $classData = S('GOODS_CLASS_DATA_WHAT');
        
        if (empty($classData)) {
            $classData = $this->field($parentField)
                ->where([
                self::$fid_d => 0,
                self::$isShow_nav_d => 0,
                self::$shoutui_d => 1
            ])
                ->order(self::$sortNum_d . self::DESC)
                ->select();
        } else {
            return $classData;
        }
        
        if (! empty($classData)) {
            S('GOODS_CLASS_DATA_WHAT', $classData, 10);
        }
        return $classData;
    }

    /**
     * 获取分类下所有的id
     *
     * @return mixed
     */
    public function selectClass($classId = 0)
    {
        $classData = $this->classData;
        $classIds = '';
        foreach ($classData as $k => $v) {
            
            if ($v['fid'] == $classId) {
                $classIds .= ',' . $v['id'];
                $classIds .= $this->selectClass($v['id']);
            }
        }
        return $classIds;
    }

    /**
     * 左边商品品牌列表
     *
     * @return mixed
     */
    public function getClassId($class = 0)
    {
        $classRs = $this->selectClass($class, $classIds);
        
        $classRs = substr($classRs, 1);
        $arr = explode(',', $classRs);
        $classIds = $classRs;
        array_unshift($arr, $class);
        return $classIds;
    }

    /**
     * 获取分类级数
     */
    public function getChildrenByParentId($fId)
    {
        static $list = array();
        showData($fId);
        $arr = $this->getClassData();
       
        foreach ($arr as $u) {
            if ($u[self::$fid_d] != $fId) {
                continue;
            }
            
            $list[] = $u[self::$id_d];
            If ($u[self::$fid_d] > 0) {
                $this->getChildrenByParentId($u[self::$id_d]);
            }
        }
        
        return $list;
    }

    public function getClassData($status = 0)
    {
        $data = S('CLASS_DATA_PARENT');
        $field = static::$id_d . ',' . static::$fid_d . ' ,' . self::$className_d;
        
        if (empty($data)) {
            $data = $this->where(self::$isShow_nav_d . ' = %d', $status)->getField($field);
        } else {
            return [];
        }
        
        if (empty($data)) {
            return array();
        }
        
        S('CLASS_DATA', $data, 45);
        return $data;
    }

    /**
     * 获取导航标题
     *
     * @param int $classId
     *            商品分类编号
     * @param string $tag
     *            标题标签
     * @return string
     */
    public function getTitleByClassId($classId, $tag)
    {
        static $number = 0;
        if (! is_numeric($classId) || $classId == 0 || $number > 3) {
            return null;
        }
        
        $number ++;
        
        $titleData = $this->field(array(
            self::$id_d,
            self::$className_d,
            self::$fid_d
        ))
            ->where(self::$id_d . '="%s"', $classId)
            ->find();
        
        if (empty($titleData)) {
            return null;
        }
        
        if ($titleData[self::$fid_d] == 0) {
            $jump_url = U("Product/ProductList", [
                "cid" => $classId
            ]);
            return '<' . $tag . '>' . '<a class="godos_details_font" href="' . $jump_url . '">' . $titleData[self::$className_d] . '</a>' . '</' . $tag . '>';
        }
        $jump_url1 = U("Product/ProductList", [
            "cid" => $classId
        ]);
        return '<' . $tag . '>' . $this->getTitleByClassId($titleData[self::$fid_d], $tag) . '</' . $tag . '>' . ' > ' . '<' . $tag . '>' . '<a href="' . $jump_url1 . '" class="godos_details_font">' . $titleData[self::$className_d] . '</a>' . '</' . $tag . '>';
    }

    /**
     * 根据分类获取用户印象
     *
     * @param int $classId
     *            分类ID
     * @return array
     */
    public function getFeelByClassId($classId)
    {
        if (empty($classId)) {
            return false;
        }
        $data = M('classFeel')->field('id as feel_id,title')
            ->where('class_id=' . $classId)
            ->select();
        return is_array($data) ? $data : [];
    }
    // 查询一级分类
    public function getClassOne()
    {
        $where['type'] = '1';
        $where['fid'] = '0';
        $data = $this->where($where)->select();
        if (empty($data)) {
            return '';
        }
        return $data;
    }
    // 根据一级分类查询下级分类
    public function getClassByClassId($class_id)
    {
        if (empty($class_id)) {
            return false;
        }
        $where['fid'] = $class_id;
        $data = $this->where($where)->select();
        if (empty($data)) {
            return '';
        }
        return $data;
    }
    // 根据商品查询分类名
    public function getClassNameByGoods(array $goods)
    {
        if (empty($goods)) {
            return false;
        }
        foreach ($goods as $key => $value) {
            $where['id'] = $value['class_id'];
            $res = $this->field('id,class_name')->find();
            $goods[$key]['class_name'] = $res['class_name'];
        }
        return $goods;
    }
    // 根据商品查询分类名
    public function getClassNameByGoodsId($goods)
    {
        if (empty($goods)) {
            return false;
        }
        $where['id'] = $goods['class_id'];
        $res = $this->field('id,class_name')->find();
        $goods['class_name'] = $res['class_name'];
        return $goods;
    }
    // 根据一级分类查询所有下级id
    public function getClassIdByFid($fid)
    {
        if (empty($fid)) {
            return false;
        }
        $field = 'id,fid';
        $res = M('goods_class')->field($field)
            ->where('fid=' . $fid)
            ->select();
        if (! empty($res)) {
            foreach ($res as $key => $value) {
                $data[] = $value['id'];
                $where['fid'] = $value['id'];
                $result = M('goods_class')->field($field)
                    ->where($where)
                    ->select();
                if (! empty($result)) {
                    foreach ($result as $k => $v) {
                        $date[] = $v['id'];
                    }
                }
            }
            $a = array_merge($data, $date);
            return $a;
        } else {
            return '';
        }
    }
    // 查出所有分类
    public function getClass()
    {
        $class = M('goods_class');
        $res = $class->field('id,class_name,fid')
            ->where('fid=0')
            ->select();
        return $res;
    }
    // 根据分类id查出分类名
    public function getClassNameByClassId($class_id)
    {
        $class = M('goods_class');
        if (empty($class_id)) {
            return false;
        }
        $res = $class->field('id,class_name,fid')
            ->where('id=' . $class_id)
            ->find();
        return $res;
    }
}