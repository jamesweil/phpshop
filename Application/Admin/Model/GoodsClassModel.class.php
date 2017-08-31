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
use Common\Model\BaseModel;
use Common\Tool\Tool;
use Common\Tool\Event;
use Common\TraitClass\FlagTrait;
use Common\Tool\Extend\UnlinkPicture;
use Think\Log;
use Common\Model\IsExitsModel;

/**
 * 商品分类模型
 */
class GoodsClassModel extends BaseModel implements IsExitsModel
{
    use FlagTrait;
    protected  $dataClass;
    
    /**
     * 更新数据
     */
    private static $obj;

    protected $patchValidate = true;
    protected $_validate = [
        ['name','require','商品分类名称不能为空'],
    ];

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

	private $totalClassData = [];
	
    /***
     * @var  array $classLevel 分类层级
     */
	private $classLevel = array();
	

    public static function getInitnation()
    {
        $class = __CLASS__;
        return  static::$obj= !(static::$obj instanceof $class) ? new static() : static::$obj;
    }

    /**
     * 获取所有数据
     */
    public function getlist(){
        $rows = $this->where(['hide_status'=>1])->select();
        return $this->getTree($rows);
    }
    
    /**
     * 根据条件 获取信息 
     */
    public function getListByCondition ($id)
    {
        if (!is_numeric($id)) {
            return array();
        }
        
        $data = $this
                ->where(static::$fid_d.'=%d and '.static::$hideStatus_d.' = 1', (int)$id)
                ->getField(static::$id_d.','.static::$className_d);
        
        return (array)$data;
    }
    
    /**
     * @param unknown $id
     * @return string[]
     */
    public function getParents($id){
        $rows = $this->where(['hide_status'=>1])->select();
        $row = $this->getTree($rows,0,0,$id);
        return $row;
    }

    /**
     * 树形菜单
     */
    public function getTree($arr,$pid=0,$deep=0,$id=-1){

        static $data = array();
        foreach($arr as $row){
            if($row['fid'] ==$pid &&$row['fid']!=$id&&$row['id']!=$id){
                $row['deep'] = $deep;
                $row ['txt'] = str_repeat("&nbsp",$deep*5).$row['class_name'];
                $data[] = $row;
                $this->getTree($arr,$row['id'],$deep+1,$id);
            }
        }
        return $data;
    }


    /**
     * 商品分类修改
     * @param array $newdata 前端提交过来的数据
     * @return bool
     */
    public function editGoodsClass(array $newdata){
        
        if (!$this->isEmpty($newdata)) {
            return false;
        }
        
        $pic = $this->where(static::$id_d.'=%d', $newdata[static::$id_d])->getField(static::$picUrl_d);
        
        if (!empty($pic) && $pic !== $newdata[static::$picUrl_d]) {//图片不同 删除原来的
            $status = Tool::partten(array($pic), UnlinkPicture::class);
            Log::write('删除分类图片是否成功（1：yes，0：no）：'.$status, Log::DEBUG);
        }
        $status = $this->save($newdata);
        return $status;
    }
    
    /**
     * 商品分类删除
     * @param int $id 商品分类id
     * @return bool|mixed
     */
    public function delGoodsClass($id){
        $rows = $this->getlist();
        foreach($rows as $row){
            //父类编号等于当前编号
            if($row['fid'] == $id){
                return false;
            }
        }
        return $this->delete($id);
    }


    /**
     * 获取商品分类 select 
     */
    public function getClassDataByStatus ()
    {
        //获取商品列表
        $classData = S('classData');
        if (empty($classData)) {
            $classData = $this->where(array(
                GoodsClassModel::$hideStatus_d => 1,
            ))->getField(GoodsClassModel::$id_d.','.GoodsClassModel::$className_d);
        
        } else {
            return $classData;
        }
        
        if (empty($classData)) {
            return array();
        }
        
        Tool::connect('PinYin');
        
        $classData = $this->firstAdd($classData);
        
        $classData = $this->sortByValue($classData); //保持键名排序
        
        S('classData', $classData, 60);
        
        return $classData;
    }
    

    /*public function save(array $data, $options = '')
    {
        if (empty($data))
        {
            return 0;
        }
        $data = $this->create($data);

        return parent::save($data, $options);
    }*/



    /**
     * 添加前操作
     */
    protected function _before_insert(&$data,$options)
    {
        $data['create_time'] = time();
        $data['type']        = 1;
        $data['update_time'] = time();
        return $data;
    }

    /**
     * 重写添加操作
     */
   /* public function add($data, array $options = array(), $replace = false)
    {
        if (empty($data))
        {
            return 0;
        }

        $data = $this->create($data);
        return parent::add($data, $options, $replace);
    }*/
    /**
     * 重写查询操作
     */
   /* public function select(array $options = array())
    {
        if (empty($options))
        {
            return array();
        }

        $data = parent::select($options);

        foreach ($data as $key => &$value)
        {
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['vo'] = parent::select(array(
                'where' => array('fid' => $value['id']),
                'field' => array('id', 'class_name', 'pic_url', 'sort_num'),
                'order' => array('create_time DESC')
            ));
        }
        return $data;
    }*/

    //获取全部编号
    public function getAllClassId(array $options)
    {
        if (empty($options))
        {
            return array();
        }

        return parent::select($options);
    }
    //更新前操作
    protected function _before_update(&$data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$className_d, $data[static::$className_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$className_d].'】';
            return false;
        }
        $data['update_time'] = time();
        return $data;
    }

    /**
     * 根据商品属性 获取数据
     * @param string $idString 分类id字符串
     * @param string $transform 要变换的字段
     * @return array
     */
    public function getClassNameByGoodsAttribute(array $attribute, $transform)
    {
        if (empty($attribute) || empty($transform)) {
            return array();
        }

        foreach ($attribute as $key => &$value)
        {
            if (!empty($value[$transform]))
            {

                $value[$transform] = $this->where(static::$id_d.'='.$value[$transform])->getField(static::$className_d);

            }
        }
        return $attribute;
    }
    /**
     * 获取全部子集分类
     * @param array $where 查询条件
     * @param array $field 查询的字段
     * @return string
     */
    public function getChildren(array $where = null, array $field = null)
    {
        // 根据地区编号  查询  该地区的所有信息
        $video_data   = parent::select(array(
            'where' => $where,
            'field' => $field,
        ));
        if (empty($video_data))
        {
            return array();
        }
        $pk    = $this->getPk();
        static $children = array();
        foreach ($video_data as $key => &$value)
        {
            if(!empty($value[$pk]))
            {
                $where['fid'] = $value[$pk];
                $child = $this->getChildren(array('fid' => $value[$pk]), $field);
                $children[$key] = $value;
                if (!empty($child))
                {
                    $children[$key]['children'] = $child;
                }
                unset($video_data[$key], $child);
            }
        }
        return $children;
    }

    /**
     * 移除分类商品
     * @param $id 分类商品的id
     * @return mixed
     */
    public function delGoodsShop($id){
        $category_ids = $this->getCategory($id);
        $category_ids = rtrim($category_ids,",");
        //删除商品分类id的商品
        $results = M("Goods")->where(['class_id'=>['in',$category_ids]])->delete();
        return $results;
    }

    /**
     * 寻找子类的id
     * @param integer $category_id 父级分类
     * @return string $category_ids 该父级分类的子类
     */
    private  function getCategory($category_id ){
        $category_ids = $category_id.",";
        $child_category = $this -> field("id,class_name")->where(['fid'=>$category_id])->select();
        foreach( $child_category as $key => $val ){
            $category_ids .= $this->getCategory( $val["id"] );
        }
        return $category_ids;
    }
    
    /**
     * 获取上一级分类数据 
     */
    public function getParentOne($id)
    {
        if (($id = intval($id)) === 0) {
            $this->error = '没有上级分类';
            return array();
        }
        
        return $this->where(static::$id_d.'=%d', $id)->getField(static::$id_d.','.static::$className_d);
        
    }
    /**
     * 获取分类级数
     * @param int $forNumber 要获取的分类级数
     */
    public function getTop(&$id, $forNumber = 2)
    {
        $data = $this->getClassData();
        $levelId = $id;
        if (empty($data)) {
            return array();
        }
        
        $flag = array();
        foreach ($data as $key => $value) {
            $flag[$key] = $value[static::$fid_d];
        }
        $level = array();
        while($flag[$id]) {
            $id = $flag[$id];
            $level[$id] = $id;
        }
        sort($level);
        $level[] = $levelId;
        if (empty($level[$forNumber])) {
            return array();
        }
        return $data[$level[$forNumber]];
    }
    
    /**
     * 获取扩展分类集合 
     * @param integer $extendClassId 扩展分类编号
     * @return array
     */
    public function getExtendCollection ($extendClassId )
    {
        if (($extendClassId = intval($extendClassId) ) === 0) {
            return array();
        }
        //------------------------扩展分类
        $extendId      = $extendClassId;
        	
        $extendSecond  = $extendClassId;
        	
        $extendThree   = $extendClassId;
        //获取顶级分类
        $extendClass = $this->getTop($extendId, 0);
        
        //二级
        $extendClassSecondData = $this->getTop($extendSecond, 1);
        //三级
        $threeClassThreeData = $this->getTop($extendThree, 2);
        Tool::isSetDefaultValue($extendClass, [
            static::$id_d => 0,
            static::$className_d => ''
        ]);
        Tool::isSetDefaultValue($extendClassSecondData, [
            static::$id_d => 0,
            static::$className_d => ' '
        ]);
        Tool::isSetDefaultValue($threeClassThreeData, [
            static::$id_d => 0,
            static::$className_d => ' '
        ]);
        $extendClassData = [
            $extendClass[static::$id_d]      => $extendClass[static::$className_d],
            $extendClassSecondData[static::$id_d]  => $extendClassSecondData[static::$className_d],
            $threeClassThreeData[static::$id_d]   => $threeClassThreeData[static::$className_d]
        ];
        
        
        $_SESSION['extendTop'] = $extendClass[static::$id_d];
        
        $_SESSION['second']    =  $extendClassSecondData[static::$id_d];
        
        return [
            'extendTop' => $extendClass[static::$id_d],
            'second'    => $extendClassSecondData[static::$id_d],
            'classData' => $extendClassData
        ];
    }
    /***/
    
    public function getClassData()
    {
        $data = S('CLASS_DATA');
        $field = static::$id_d.','.static::$fid_d.','.static::$className_d;
        
        Event::listen('parseFieldGoodsClass', $field);//监听
        
        if (empty($data)) {
            $data = $this->getField($field);
            if (empty($data)) {
                return array();
            }
            S('CLASS_DATA', $data, 15);
        }
        
        return $data;
    }
    
    /**
     *获取 一二级分类
     */
    public function getOneAndSecondClass ()
    {
        $data = S('ONE_AND_SECOND_CLASS_DATA');
        $field = $this->trueTableName.'.'.static::$id_d.','.$this->trueTableName.'.'.static::$className_d;
        
        
        if (empty($data)) {
            $data = $this->where(static::$fid_d.'= 0')->getField(static::$id_d.','.static::$className_d);
            if (empty($data)) {
                return array();
            }
            
            $pIdString = implode(',', array_keys($data));
            
            $second = (array)$this->where(static::$fid_d.' in ('.addslashes($pIdString).')')->getField(static::$id_d.','.static::$className_d);
           
            
            foreach ($second as $key => $value)
            {
                $data[$key] = $value;
            }
            
            S('ONE_AND_SECOND_CLASS_DATA', $data, 15);
        }
        return $data;
    }
    
    /**
     *  重组分类数据
     */
    public function buildClass ()
    {
        $array = array();
        $data = $this->getDataByPage(array(
            'field' => array(
                static::$cssClass_d,
            ),
            'where' => [static::$fid_d => 0],
            'order' => static::$sortNum_d.static::DESC.','.static::$createTime_d.static::DESC
        ), C('PAGE_NUMBER'), true);
        
        if (empty($data)) {
            return array();
        }
        
        $second = $this->getNextClass($data['data']);
        
        $three  = $this->getNextClass($second);
        
        $data['data'] = array_merge($this->totalClassData, $data['data']);
        
        $data['data'] = Tool::connect('Tree')->makeTreeForHtml($data['data'], array(
            'parent_key' => static::$fid_d
        ));
        
        $data['data'] = $this->covertKeyById($data['data'], static::$id_d);
        
        $this->dataClass = $data['data'];
        
        //是否有子级
        foreach ($data['data'] as $key => & $value) {
           if ($value['level'] == 0) {
               $this->isHaveSon($array, $value[static::$id_d]);
           }
        }
        $flagArray = array();
        $flagArray['data'] = $array;
        $flagArray['page'] = $data['page'];
        return $flagArray;
    }
    
    /**
     * 获取一级分类 
     */
    public function getTopClass ()
    {
        return $this->where(static::$fid_d.' = 0 and '.static::$hideStatus_d.' = 1')->getField(static::$id_d.','.static::$className_d);
    }
    
    /**
     * 根据编号 获取分类 
     */
    public function getClassById ($id)
    {
        if ( ($id = intval($id)) === 0) {
            return array();
        }
        return $this->field(static::$id_d.','.static::$className_d)->where(static::$fid_d.' = %d and '.static::$hideStatus_d.' = 1', $id)->select();
    }
    
    /**
     * 获取下级分类 
     */
    public function getNextClass (array $data)
    {
        if (!$this->isEmpty($data)) {
            return array();
        }
        
        $idString = Tool::characterJoin($data, static::$id_d);
        
        $second = $this->field(static::$cssClass_d, true)->where(static::$fid_d.' in ('.$idString.')')->select();
        
        if (empty($second)) {
            return array();
        }
        
        foreach ($second as $key => $value) {
            $data[$key] = $value;
        }
        
        $this->totalClassData = array_merge($this->totalClassData, $data);
        
        return $second;
    }
    /**
     * {@inheritDoc}
     * @see \Common\Model\IsExitsModel::IsExits()
     */
    public function IsExits($post)
    {
        // TODO Auto-generated method stub
        
        if (empty($post[self::$className_d])) {
            return true;
        }
        
        $isExits = $this->where(self::$className_d.'="%s"', $post[self::$className_d])->getField(self::$id_d);
        
        return empty($isExits) ? false : true;
    }
}