<?php
namespace Home\Logical\Model;

use Home\Model\GoodsCartModel;
use Common\Tool\Tool;
use Common\TypeParse\AbstractParse;
use Common\Logic\AbstractGetDataModel;

/**
 * Array
 * (
 * [data] => Array
 * (
 * [0] => Array
 * (
 * [goods_id] => 1245
 * [price_new] => 30.00
 * [goods_num] => 1
 * )
 *
 * [1] => Array
 * (
 * [goods_id] => 950
 * [price_new] => 5.00
 * [goods_num] => 1
 * )
 *
 * [2] => Array
 * (
 * [goods_id] => 952
 * [price_new] => 5.00
 * [goods_num] => 1
 * )
 *
 * )
 *
 * )
 * 
 * @author Administrator
 *        
 */
class GoodsCartLogic extends AbstractGetDataModel
{

    
    private $userId = 0;
    
    private static $cartObj;
    
    /**
     * @return the $userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param number $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    private function __construct(array $data, $userId = 0)
    {
        $this->modelObj = GoodsCartModel::getInitnation();
        
        $this->data = $data;
        
        $this->userId = $userId === 0 ? $_SESSION['user_id'] : $userId;
    }

    static public function getInstance(array $data, $userId = 0)
    {
        $class = __CLASS__;
        if (! (self::$cartObj instanceof $class)) {
            static::$cartObj = new static($data, $userId);
        }
        
        return static::$cartObj;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Home\Logical\AbstractGetDataModel::getResult()
     */
    public function getResult()
    {
        // TODO Auto-generated method stub
        $data = $this->data;
        
        if (empty($data)) {
            return false;
        }
        
        //处理商品信息
        $data = $this->modelObj->covertKeyById($data, GoodsCartModel::$goodsId_d);//商品信息
       
        $idString = $this->getGoodsCartIdString();//goods_id
        $cartData = $this->getCartDataByIds($idString);
        $obj = AbstractParse::getInstance($cartData);
        $obj->setModel($this);
        $status = $obj->parseGoodsCart($data);
        return $status;
        
    }
    
    
    
    protected function getCartDataByIds ($idString)
    {
        $field = GoodsCartModel::$id_d.','.GoodsCartModel::$goodsId_d.','.GoodsCartModel::$goodsNum_d;
        
        return $this->modelObj->where(GoodsCartModel::$goodsId_d .' in ('.$idString.') and '.GoodsCartModel::$buyType_d.'= 1 and '.GoodsCartModel::$userId_d.'=%d', $this->userId)->getField($field);
    }
    
    protected function getGoodsCartIdString ()
    {
        return Tool::characterJoin($this->data, GoodsCartModel::$goodsId_d);
    }
    
    /**
     * 表里的数据
     */
    public function paeseCartByGoodsData($data)
    {
        $postData = $this->data;
        
        if (empty($postData)) { // 批量更新
            
            return $this->UpdateAll($data);
        }
        
        $obj = $this->modelObj;
        
        $this->modelObj->startTrans();
        
        //批量添加
        
        $status = $this->addAll($postData);
        
        if (!$obj->traceStation($status, '添加失败')) {
            return false;
        }
        
        //批量更新
        
        $status = $this->UpdateAll($data);
        
        if (!$obj->traceStation($status, '添加失败')) {
            return false;
        }
        
        $obj->commit();
        
        return $status;
    }
    
    /**
     * 批量更新
     * @param array $data 表数据
     */
    protected function UpdateAll(array $data)
    {
        if (empty($data)) {
            return false;
        }
        
        $updateKey = [
            GoodsCartModel::$goodsNum_d
        ];
        
        $temp = [];
        
        foreach ($data as $key => $value)
        {
            $temp[$value[GoodsCartModel::$id_d]][] = $value[GoodsCartModel::$goodsNum_d];
        }
        
        $obj = $this->modelObj;
        
        $table = $obj->getTableName();
        
        $sql = $obj->buildUpdateSql($temp, $updateKey,$table);
        return $obj->execute($sql);
    }
    
    /**
     * 批量添加: Array
(
    [1245] => Array
        (
            [goods_id] => 1245
            [price_new] => 30.00
            [goods_num] => 1
        )

    [950] => Array
        (
            [goods_id] => 950
            [price_new] => 5.00
            [goods_num] => 1
        )

    [952] => Array
        (
            [goods_id] => 952
            [price_new] => 5.00
            [goods_num] => 1
        )

)
     */
    public function addAll (array $data)
    {
       
        if (empty($data)) {
            return false;
        }
        
        foreach ($data as & $value)
        {
            $value[GoodsCartModel::$userId_d] = $this->userId;
             
            $value[GoodsCartModel::$updateTime_d] = time();
        
            $value[GoodsCartModel::$createTime_d] = time();
        
            $value[GoodsCartModel::$buyType_d] = 1;
        }
        
        sort($data);
        
        return $this->modelObj->addAll($data);
    }
}