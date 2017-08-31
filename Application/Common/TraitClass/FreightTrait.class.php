<?php
namespace Common\TraitClass;


use Think\Controller;
use Common\Tool\Tool;
use Common\Model\BaseModel;
use Common\Model\UserAddressModel;
use Home\Model\FreightModeModel;
use Home\Model\FreightsModel;
use Home\Model\FreightConditionModel;
use Home\Logical\Model\FreightSendAreaLogic;
use Home\Logical\Model\FreightAreaLogic;
use Common\Strategy\FreightContent;
use Common\Tool\Extend\ArrayParse;





//运费Trait
/**
 * Class FreightTrait
 * @package Common\TraitClass
 *          $_POST = [
 *          id -> 快递id
 *          addressid  ->  收货地址id
 *          discount  ->   折扣
 *           ]
 */
trait FreightTrait
{
    /**
     * 计算运费
     */
    public function sumFreight()
    {
        $validate = ['is_numeric' => ['id', 'addressId', 'discount']];

        Tool::checkPost($_POST, $validate, true, array(
            'id'
        )) ? true : $this->ajaxReturnData(null, 0, '快递方式错误');

        $this->promptPjax( $_SESSION['user_goods_monery'], '商品金额有误');

        //获取收货地址
        $userAddressMode   = BaseModel::getInstance(UserAddressModel::class);

        $areaId            = $userAddressMode->getOne($_POST['addressId'], UserAddressModel::$city_d . ',' . UserAddressModel::$provId_d);

        $this->promptPjax($areaId, '用户地址错误');

        //首先获取 此运送方式的所有运费设置
        $freightModel = BaseModel::getInstance(FreightModeModel::class);

        $freightModeList = $freightModel->getShipModeConfig($_POST['id']);

        $this->promptPjax($freightModeList, '运费方式没有对应的收费标准~~~');

        Tool::connect('parseString');

        //筛选是否在运送地区内
        $freightSendAreaModel  = new FreightSendAreaLogic($freightModeList);

        $freightSendAreaModel->setAreaId($areaId);

        $freightModeInAreaList = $freightSendAreaModel->isInclude($freightModel);



        $this->promptPjax($freightModeInAreaList, '该快递不包含该运送地区');

        //筛选是否包邮
        $freightConditionModel = BaseModel::getInstance(FreightConditionModel::class);

        $freightConditionList  = $freightConditionModel->IsInFreeShipingArea($freightModeInAreaList, $freightModel);


        //获取运费模板数据
        $freightTemplateModel = BaseModel::getInstance(FreightsModel::class);

        $freightConditionList = $freightTemplateModel->isFreeShipping($freightConditionList, $freightModel);


        //是否在包邮地区内
        $freightAreaModel = new FreightAreaLogic($freightConditionList);

        $freightAreaModel->setAreaId($areaId);

        $freightMonery  = $freightAreaModel->isInclude(); //在包邮地区内

        if (empty($freightMonery)) {

            $freightMonery = $freightTemplateModel->isFreeShipping($freightModeInAreaList, $freightModel);

        }
        $receive = array();

        $freightMonery = (new ArrayParse([]))->oneArray($receive, $freightMonery);
        // 计算运费
        $type = FreightContent::parseCall($receive[FreightsModel::$valuationMethod_d]);
        $obj = FreightContent::getInstance($type, $receive)->newInstance();

        $obj->setDiscount($_POST['discount']);

        $money = $obj->acceptCash();
        $_SESSION['FreightMoney'] = $money;//商品的运费

        $this->ajaxReturnData(['money' => $money]);

    }

}