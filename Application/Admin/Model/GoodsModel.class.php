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
namespace Admin\Model;

use Common\Tool\Tool;
use Common\Model\BaseModel;
use Common\TraitClass\callBackClass;
use Common\TraitClass\MethodTrait;

class GoodsModel extends BaseModel
{
    use callBackClass;
    
    use MethodTrait;

    private static $obj;

    protected $idArray = array();

    protected $selectColum;

	public static $id_d;	//主键编号

	public static $brandId_d;	//品牌编号

	public static $title_d;	//商品标题

	public static $priceMarket_d;	//市场价

	public static $priceMember_d;	//会员价

	public static $stock_d;	//库存

	public static $selling_d;	//是否是热销   0 不是   1 是

	public static $shelves_d;	//0下架，1表示选择上架

	public static $classId_d;	//商品分类ID

	public static $recommend_d;	//1推荐 0不推荐

	public static $dIntegral_d;	//赠送积分

	public static $code_d;	//商品货号

	public static $top_d;	//顶部推荐

	public static $seasonHot_d;	//当季热卖

	public static $restrictions_d;	//是否限购:    1 限购  0 不限购

	public static $description_d;	//商品简介

	public static $groupBuy_d;	//是否团购 默认0 不团购 1是

	public static $updateTime_d;	//最后一次编辑时间

	public static $createTime_d;	//创建时间

	public static $goodsType_d;	//商品类型

	public static $latestPromotion_d;	//最新促销：1表示热卖促销，2表示热卖精选，3表示人气特卖

	public static $sort_d;	//排序

	public static $pId_d;	//父级产品 SPU

	public static $status_d;	//0没有活动，1尾货清仓，2，最新促销，3积分商城,4打印耗材,5优惠套餐

	public static $commentMember_d;	//评论次数

	public static $salesSum_d;	//商品销量

	public static $attrType_d;	//商品属性编号【为goods_type表中数据】

	public static $extend_d;	//扩展分类

	public static $advanceDate_d;	//预售日期

	public static $weight_d;	//重量


    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = ! (static::$obj instanceof $name) ? new static() : static::$obj;
    }

    /**
     * 重写父类方法
     */
    protected function _before_insert(& $data, $options)
    {
        $data[static::$createTime_d] = time();
        $data[static::$updateTime_d] = time();
        
        $data[static::$sort_d] = 50;
        
        return $data;
    }

    /**
     * 重写父类方法
     */
    protected function _before_update(& $data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$title_d, $data[static::$title_d]);
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【' . $data[static::$title_d] . '】';
            return false;
        }
        $data[static::$updateTime_d] = time();
        
        return $data;
    }

    /**
     * add
     */
    public function add($data = '', $options = array(), $replace = false)
    {
        if (empty($data[static::$classId_d])) {
            
            return false;
        }
        
        // $flag = static::flag($data, static::$classId_d);
        
        // // $data[static::$classId_d] = $flag;
        
        // $data = $this->create($data);
        
        // 新加字段天数转为时间戳即为预售日期时间戳
        if ($data['stock'] == 0) {
            $data['advance_date'] = time() + $data['advance_date'] * 24 * 60 * 60;
        } else {
            $data['advance_date'] = $data['advance_date'] * 24 * 60 * 60;
        }
        return parent::add($data, $options, $replace);
    }

    /**
     * save
     * /**
     * UPDATE
     * db_goods as goods,
     * db_goods as product
     * SET goods.title = CASE goods.id
     * WHEN 1092 THEN
     * 'ooo'
     * WHEN 1093 THEN 'qqq'
     * END,
     * //product.title = 'eee'
     * WHERE goods.p_id = product.id and goods.p_id=1091 and product.id=1091;
     */
    public function saveGoods(array $data)
    {
        if (empty($data)) {
            return false;
        }
        
        // $flag = static::flag($data, static::$classId_d);
        
        // $data[static::$classId_d] = $flag;
        
        // 先查出子集商品
        $child = $this->field([
            static::$id_d,
            static::$title_d,
            static::$brandId_d,
            static::$classId_d
        ])
            ->where(static::$pId_d . '=%d', (int) $data[static::$id_d])
            ->select();
        $this->startTrans();
        
        if (empty($child)) {
            return $this->save($data);
        }
        // 获取要更新的字段
        $keyArray = [
            static::$id_d,
            static::$title_d,
            static::$brandId_d,
            static::$classId_d,
            static::$extend_d
        ];
        
        $itemData = $this->covertKeyById($data['item'], 'goods_id');
        
        if (empty($itemData)) {
            $this->rollback();
            return false;
        }
        
        $arr = array();
        foreach ($child as $key => $value) {
            $arr[$value[static::$id_d]][] = $value[static::$id_d];
            $arr[$value[static::$id_d]][] = $itemData[$value[static::$id_d]][static::$title_d];
            $arr[$value[static::$id_d]][] = $data[static::$brandId_d];
            $arr[$value[static::$id_d]][] = $data[static::$classId_d];
            $arr[$value[static::$id_d]][] = empty($data[static::$extend_d]) ? 0 : $data[static::$extend_d];
            
            if ((int) $value[static::$stock_d] !== 0) { // //新加字段天数转为时间戳即为预售日期时间戳
                
                $arr[$value[static::$id_d]][] = ($data[static::$advanceDate_d] * 24 * 60 * 60);
            } else {
                if (floor(($value[static::$advanceDate_d] - time()) / (24 * 60 * 60)) == $data[static::$advanceDate_d]) {
                    $arr[$value[static::$id_d]][] = $value[static::$advanceDate_d];
                } else {
                    $arr[$value[static::$id_d]][] = time() + ($value[static::$advanceDate_d] * 24 * 60 * 60);
                }
            }
        }
        
        if (empty($arr)) {
            $this->rollback();
            return false;
        }
        
        // 天加监听事件
        $table = $this->getTableName();
        
        $sql = $this->buildUpdateSql($arr, $keyArray, $table);
        
        $status = parent::execute($sql);
        if (! $this->traceStation($status)) {
            return false;
        }
        // 新加字段天数转为时间戳即为预售日期时间戳
        if ($data['stock'] == 0) {
            $before_date = M('goods')->field('advance_date')
                ->where('id=' . $data['id'])
                ->find();
            if (floor(($before_date['advance_date'] - time()) / (24 * 60 * 60)) == $data['advance_date']) {
                $data['advance_date'] = $before_date['advance_date'];
            } else {
                $data['advance_date'] = time() + ($data['advance_date'] * 24 * 60 * 60);
            }
        } else {
            $data['advance_date'] = ($data['advance_date'] * 24 * 60 * 60);
        }
        $status = $this->save($data);
        if (! $this->traceStation($status)) {
            return false;
        }
        
        return true;
    }

    /**
     * 求和,求出商品推荐类型的位运算值.
     * 
     * @param type $shelves            
     * @return int
     */
    protected function calcShelves($shelves)
    {
        if (isset($shelves)) {
            return array_sum($shelves);
        } else {
            return 0;
        }
    }

    /**
     * 根据订单信息 查询商品数据
     */
    public function getOrderInfo(array $data, $field, $fiter = FALSE)
    {
        if (empty($data) || empty($field)) {
            return array();
        }
        usort($data, array(
            $this,
            'compare'
        ));
        $id = Tool::characterJoin($data);
        
        if (empty($id)) {
            return array();
        }
        
        $dataArray = $this->field($field, $fiter)
            ->where('id in (' . $id . ')')
            ->select();
        
        if (empty($data)) {
            return array();
        }
        usort($dataArray, array(
            $this,
            'compare'
        ));
        return $data = Tool::parseTwoArray($data, $dataArray);
    }

    /**
     * 删除商品
     * 
     * @param int $id
     *            商品的id
     * @return bool
     */
    public function delGoods($id)
    {
        if (($id = intval($id)) === 0) {
            return false;
        }
        
        // 查找父级
        $pId = $this->where(static::$pId_d . '=%d', $id)->getField(static::$id_d . ',' . static::$pId_d);
        if (empty($pId)) {
            return $this->delete($id);
        }
        
        $this->startTrans();
        
        $idArray = array_keys($pId);
        
        $status = $this->where(static::$id_d . ' in (' . implode(',', array_keys($pId)) . ',' . $id . ')')->delete();
        
        if ($status === false) {
            $this->rollback();
            return false;
        }
        
        return $idArray;
    }

    /**
     * 删除子类商品
     * 
     * @param int $id            
     * @return boolean
     */
    public function deleteGoodById($id)
    {
        if (($id = intval($id)) === 0) {
            return false;
        }
        
        $this->startTrans();
        
        $status = $this->where(static::$id_d . '= %d', $id)->delete();
        
        return $this->traceStation($status);
    }

    public function getInfoGoods($id)
    {
        // 获取商品的基本信息
        $row = $this->find($id);
        // 由于在前端展示的时候,需要使用到2个状态,所以我们变成一个json对象
        $tmp = [];
        if ($row['shelves'] & 1) {
            $tmp[] = 1;
        }
        if ($row['shelves'] & 2) {
            $tmp[] = 2;
        }
        $row['shelves'] = json_encode($tmp);
        unset($tmp);
        
        return $row;
    }

    /**
     * 根据规格生成对应产品
     * 
     * @param array $data
     *            商品全数据
     * @return array 主键数组
     */
    public function addSpecDdataByGoods(array $data, BaseModel $model, $item = 'item')
    {
        if (empty($data[$item]) || ! ($model instanceof BaseModel) || empty($data[$model::$goodsId_d])) {
            return $data;
        }
        
        $item = $data[$item];
        
        // 处理价格
        $build = array();
        foreach ($item as $key => & $value) {
            
            $data[static::$priceMarket_d] = $value[$model::$price_d];
            
            $data[static::$priceMember_d] = $value[$model::$preferential_d];
            
            $data[static::$stock_d] = $value[$model::$storeCount_d];
            
            $data[static::$updateTime_d] = time();
            
            $data[static::$createTime_d] = time();
            if (! empty($value[static::$title_d])) {
                $data[static::$title_d] = $value[static::$title_d];
            }
            
            $build[] = $this->create($data);
        }
        if (empty($build)) {
            return $data;
        }
        
        foreach ($build as $key => & $value) {
            // $value[static::$classId_d] = static::flag($value, static::$classId_d);
            $value[static::$pId_d] = $data[$model::$goodsId_d];
        }
        $this->startTrans();
        
        $insertId = $this->addAll($build);
        if (! $this->traceStation($insertId)) {
            return false;
        }
        
        $this->where(static::$pId_d . '= %d or ' . static::$id_d . '= %d', [
            $data[$model::$goodsId_d],
            $data[$model::$goodsId_d]
        ])->save([
            static::$goodsType_d => $data[static::$goodsType_d]
        ]);
        $count = count($build);
        $number = array();
        for ($i = 0; $i < $count; $i ++) {
            $number[$i] = $i + $insertId;
        }
        return $number;
    }

    /**
     * 更新数据
     * 
     * @param array $data
     *            form数据
     * @param BaseModel $model
     *            模型对象
     * @param string $item
     *            要截取的 键
     *            <pre>
     *            /**
     *            update db_goods set price =
     *            
     *            CASE id
     *            WHEN 1029 THEN 4.00
     *            WHEN 1028 THEN 7.00
     *            END,
     *            
     *            stock = CASE id
     *            WHEN 1029 THEN 5
     *            WHEN 1028 THEN 8
     *            END
     *            where id in(1029,1028);
     *            </pre>
     */
    public function updateData(array $data, BaseModel $model, $item = 'item')
    {
        if (empty($data[$item]) || ! ($model instanceof BaseModel)) {
            return $data;
        }
        
        $specData = & $data[$item];
        $table = $this->getTableName();
        
        $parseData = array();
        
        foreach ($specData as $key => & $value) {
            if (! empty($value[$model::$goodsId_d])) {
                
                $parseData[$key][static::$priceMarket_d] = $value[$model::$price_d];
                
                $parseData[$key][static::$priceMember_d] = $value[$model::$preferential_d];
                
                $parseData[$key][static::$stock_d] = $value[$model::$storeCount_d];
                
                $parseData[$key][static::$id_d] = $value[$model::$goodsId_d];
                unset($specData[$key]);
            }
        }
        
        if (! empty($data[$item])) {
            
            $data[$model::$goodsId_d] = $data[static::$id_d];
            unset($data[static::$id_d]);
            
            $this->idArray = $this->addSpecDdataByGoods($data, $model, $item);
            
            if (empty($this->idArray)) {
                $this->rollback();
            }
        }
        
        if (empty($parseData)) {
            return $this->idArray;
        }
        
        $this->startTrans();
        $keyArray = array();
        
        // 获取要更新的字段
        foreach ($parseData as $key => $value) {
            unset($value[static::$id_d]);
            $keyArray = array_keys($value);
        }
        
        unset($specData);
        
        /**
         *
         * @var array $array array(
         *      17 => array(
         *      8, 5,
         *      ),
         *      18 => array(
         *      7.00,4.00
         *      ),
         *      )
         */
        
        $arr = array();
        
        foreach ($parseData as $key => $value) {
            $arr[$value[static::$id_d]][] = $value[static::$priceMarket_d];
            $arr[$value[static::$id_d]][] = $value[static::$priceMember_d];
            $arr[$value[static::$id_d]][] = $value[static::$stock_d];
        }
        
        $sql = $this->buildUpdateSql($arr, $keyArray, $table);
        $status = parent::execute($sql);
        
        if (! $this->traceStation($status)) {
            return false;
        }
        return $status;
    }

    /**
     * 创造where 条件[122111423]
     * 
     * @param array $data
     *            筛选数据
     * @return array
     */
    public function bulidWhere(array $data)
    {
        if (empty($data) || ! is_array($data)) {
            return array();
        }
        
        $data = $this->create($data);
        
        $data = Tool::buildActive($data);
        
        if (! empty($data[static::$title_d])) {
            $data[static::$title_d] = array(
                'like',
                '%' . $data[static::$title_d] . '%'
            );
        }
        return $data;
    }

    /**
     * 更新商品属性
     */
    public function saveAttrType(array $post)
    {
        if (! $this->isEmpty($post)) {
            return false;
        }
        
        $this->startTrans();
        
        $goodsId = (int) $_POST['goods_id'];
        
        $status = $this->where(static::$pId_d . '= %d or ' . static::$id_d . '= %d', [
            $goodsId,
            $goodsId
        ])->save([
            static::$attrType_d => $_POST[static::$attrType_d]
        ]);
        return $this->traceStation($status);
    }

    /**
     * 拼接数据
     */
    public function innerJoin($id, $splitKey)
    {
        if ($id == 0 || ! is_numeric($id) || ! is_string($splitKey)) {
            return array();
        }
        
        $children = $this->getAttribute(array(
            'field' => array(
                GoodsModel::$id_d
            ),
            'where' => array(
                GoodsModel::$pId_d => $id
            )
        ));
        
        return Tool::characterJoin($children, $splitKey);
    }

    /**
     * 获取父类产品
     * 
     * @param int $id
     *            父级编号
     * @param array $field
     *            要查询的字段
     * @param BaseModel $model
     *            基类 模型
     * @return array【只许一次】
     */
    public function getGoodsDataByParentId($id, BaseModel $model)
    {
        if (($id = intval($id)) === 0 || ! ($model instanceof BaseModel)) {
            return array();
        }
        
        $field = [
            static::$id_d,
            static::$title_d,
            static::$code_d,
            static::$classId_d,
            static::$priceMarket_d,
            static::$priceMember_d,
            static::$stock_d,
            static::$shelves_d,
            static::$recommend_d
        ];
        
        $data = $this->getAttribute(array(
            'field' => $field,
            'where' => array(
                static::$pId_d => $id
            ),
            'order' => static::$createTime_d . static::DESC . ',' . static::$updateTime_d . static::DESC
        ));
        
        if (empty($data)) {
            return array();
        }
        
        $data = $model->getDataByOtherModel($data, static::$classId_d, array(
            $model::$id_d,
            $model::$className_d
        ), $model::$id_d);
        return $data;
    }

    /**
     * 根据退货信息 获取 商品数据
     * 
     * @param array $data
     *            退货数据
     * @param string $split
     *            分割键
     * @return array
     */
    public function getGoodsByOrderReturn(array $data, $split)
    {
        if (! $this->isEmpty($data) || empty($split)) {
            return array();
        }
        
        $cache = S('GOODS_DATA_CACHE_NAME');
        
        if (empty($cache)) {
            
            $cache = $this->getDataByOtherModel($data, $split, [
                static::$id_d,
                static::$title_d
            ], static::$id_d);
            
            if (empty($cache)) {
                return array();
            }
            
            S('GOODS_DATA_CACHE_NAME', $cache, 2);
        }
        return $cache;
    }

    /**
     * 根据订单信息 获取单挑数据
     */
    public function getReturnGoods($id)
    {
        if (($id = intval($id)) === 0) {
            return null;
        }
        
        $data = $this->field([
            static::$createTime_d,
            static::$updateTime_d
        ], true)
            ->where(static::$id_d . '=%d', $id)
            ->find();
        
        if (empty($data)) {
            return array();
        }
        
        return empty($data[$this->selectColum]) ? $data : $data[$this->selectColum];
    }

    /**
     * 获取商品数据
     */
    public function getGoodsData(array $data, $split)
    {
        if (! $this->isEmpty($data)) {
            return array();
        }
        
        $data = $this->getDataByOtherModel($data, $split, [
            static::$title_d,
            static::$id_d
        ], static::$id_d);
        
        return $data;
    }

    /**
     * 修改商品活动状态
     */
    public function editStatus($goosId, $status = 0)
    {
        $status = $this->editPoopStatus($goosId);
        if (! $this->traceStation($status)) {
            return false;
        }
        
        $this->commit();
        
        return $status;
    }

    /**
     * 修改 促销状态
     * 
     * @param int $goosId            
     * @param int $status            
     */
    public function editPoopStatus($goosId, $status = 0)
    {
        $int = [
            $goosId,
            $status
        ];
        if (! $this->foreachDataTypeIsEmpty($int)) {
            $this->rollback();
            return false;
        }
        $status = $this->save([
            static::$id_d => $goosId,
            static::$status_d => $status
        ]);
        return $status;
    }

    /**
     * 根据促销条件获取匹配的商品
     * 
     * @param array $where
     *            条件
     * @return array
     */
    public function getPromotionGoodsByOption(array $where = array())
    {
        $funArray = C('pro_type');
        
        $fun = $funArray[$_GET['type']];
        
        $where = array_merge($where, $this->$fun());
        
        $where[self::$status_d] = 0; // 搜索的没有活动的产品
        
        $goodsData = $this->getDataByPage(array(
            'field' => array(
                self::$id_d,
                self::$title_d,
                self::$priceMember_d,
                self::$stock_d
            ),
            'where' => $where,
            'order' => self::$createTime_d . self::DESC . ',' . self::$updateTime_d . self::DESC
        ));
        
        return $goodsData;
    }

    /**
     * 价格 条件
     */
    protected function lt()
    {
        return [
            self::$priceMember_d => [
                'lt',
                $_GET['price']
            ]
        ];
    }

    /**
     * 固定金额出售[优惠金额]
     */
    protected function gt()
    {
        return [
            self::$priceMember_d => [
                'gt',
                $_GET['price']
            ]
        ];
    }

    /**
     * 打折
     */
    protected function undefined()
    {
        return [];
    }

    /**
     * 批量设置状态[事务]
     * 
     * @param array $data            
     * @param int $status            
     */
    public function setGoodsStatus(array $data)
    {
        if (! $this->isEmpty($data)) {
            $this->rollback();
            return false;
        }
        
        $key = [ // 要更新的键
            self::$status_d
        ];
        
        $temp = array();
        
        // 组装批量更新语句
        foreach ($data as $value) {
            $temp[$value][] = self::PROMOTION;
        }
        
        $isPuss = $this->saveStatus($temp, $key);
        
        return true;
    }

    /**
     * 辅助优惠促销方法
     * 
     * @param array $temp            
     * @param array $key            
     * @return boolean
     */
    private function saveStatus(array $temp, array $key)
    {
        $tableName = $this->getTableName();
        
        $sql = $this->buildUpdateSql($temp, $key, $tableName);
        $status = $this->execute($sql);
        
        if (! $this->traceStation($status)) {
            return false;
        }
        return true;
    }

    /**
     * 促销编辑时 商品状态处理
     * 
     * @param array $data            
     */
    public function validateGoodsStatus(array $data)
    {
        if (! $this->isEmpty($data)) {
            $this->rollback();
            return false;
        }
        $validateData = $this->compareDataByArray($data); // 验证是否更新商品状态
        
        if (empty($data)) {
            return true;
        }
        
        $temp = array();
        foreach ($validateData as $value) {
            if (! in_array($value, $data, true)) {
                $temp[$value][] = self::NOACTIVITY;
            } else {
                $temp[$value][] = self::PROMOTION;
            }
        }
        $key = [
            self::$status_d
        ];
        
        $status = $this->saveStatus($temp, $key);
        
        return $status;
    }

    /**
     * 获取分页数据
     */
    public function getPageByGoodsData(array $where)
    {
        $pageNumber = C('PAGE_SETTING.ADMIN_GOODS_LIST');
        $field = [
            'id',
            'title',
            'code',
            'class_id',
            'recommend',
            'price_market',
            'price_member',
            'sort',
            'stock',
            'shelves',
            'latest_promotion',
            'create_time'
        ];
        
        $data = $this->getDataByPage([
            'field' => $field,
            'where' => $where,
            'order' => self::$id_d . self::DESC . ', ' . self::$sort_d . self::DESC
        ], $pageNumber);
        
        return $data;
    }

    /**
     * 获取商品数量[只获取子类商品的，因为 父类商品不显示 只是起到纽带作用]
     */
    public function getGoodsTotal()
    {
        return $this->where(self::$pId_d . ' > 0')->count();
    }

    /**
     * 修改 商品及其子类 的上架状态
     * 
     * @param array $post            
     * @return boolean
     */
    public function saveData(array $post)
    {
        if (! $this->isEmpty($post)) {
            return false;
        }
        
        $goodsId = (int) $post[static::$id_d];
        
        unset($post[static::$id_d]);
        
        return $this->where(static::$id_d . '=%d or ' . static::$pId_d . '=%d', [
            $goodsId,
            $goodsId
        ])->save($post);
    }

    /**
     *
     * @return the $selectColum
     */
    public function getSelectColum()
    {
        return $this->selectColum;
    }

    /**
     *
     * @param field_type $selectColum            
     */
    public function setSelectColum($selectColum)
    {
        $this->selectColum = $selectColum;
    }

    /**
     *
     * @return the $idArray
     */
    public function getIdArray()
    {
        return $this->idArray;
    }

    /**
     *
     * @param multitype: $idArray            
     */
    public function setIdArray($idArray)
    {
        $this->idArray = $idArray;
    }
    
    /**
     * 最新促销
     *
     * @var int
     */
    const PROMOTION = 0x02;
    
    /**
     * 没有活动
     *
     * @var int
     */
    const NOACTIVITY = 0x00;
}