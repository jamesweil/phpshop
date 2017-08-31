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

/**
 * Created by PhpStorm.
 * User: x
 * Date: 2016/8/12
 * Time: 14:52
 */
namespace Admin\Controller;

use Common\Controller\AuthController;
use Common\Model\BaseModel;
use Common\Model\ExpressModel;
use Common\Tool\Tool;
use Admin\Model\SendAddressModel;
use Common\Model\RegionModel;
use Common\TraitClass\AddressTrait;

class FreightController extends AuthController
{
    use AddressTrait;

    protected $status = 0;

    /**
     * 发货地址列表
     */
    public function sendGoodsList()
    {
        $model = BaseModel::getInstance(SendAddressModel::class);
        
        Tool::connect('ArrayChildren');
        
        $where = $model->buildSearch($_GET, true);
        $region = BaseModel::getInstance(RegionModel::class);
        
        // if (empty($data)) {
        $data = $model->getDataByPage(array(
            'field' => implode(',', $model->getDbFields()),
            'where' => $where
        ));
        
        Tool::connect('parseString');
        
        $data['data'] = $region->getArea($data['data'], SendAddressModel::$addressId_d);
        Tool::isSetDefaultValue($_GET, [
            SendAddressModel::$stockName_d => ''
        ]);
        $this->sendModel = SendAddressModel::class;
        
        $this->data = $data;
        $this->regionModel = RegionModel::class;
        
        $this->display();
    }

    /**
     * 是否启用
     */
    public function isOpen()
    {
        $validate = array(
            'id',
            'status'
        );
        
        $this->setStatus($validate);
    }

    /**
     * 是否默认
     */
    public function isDefault()
    {
        $validate = array(
            'id',
            'def'
        );
        
        $this->status = 1;
        
        $this->setStatus($validate);
    }

    private function setStatus(array $validate)
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => $validate
        ), true, $validate) ? true : $this->ajaxReturnData(null, 0, '防御系统启动');
        
        $model = BaseModel::getInstance(SendAddressModel::class);
        if ($this->status === 1) {
            
            $status = $model->setDefault($_POST);
            
            $this->updateClient($status, '操作');
            die();
        }
        
        $status = $model->save($_POST);
        
        $this->updateClient($status, '操作');
    }

    /**
     * 添加发货地址
     */
    public function addSendAddress()
    {
        $model = BaseModel::getInstance(SendAddressModel::class);
        
        $this->sendModel = SendAddressModel::class;
        
        $this->display();
    }

    /**
     * 编辑
     * 
     * @param int $id
     *            仓库编号
     */
    public function editHtml($id)
    {
        $this->errorNotice($id);
        
        $model = BaseModel::getInstance(SendAddressModel::class);
        
        // 获取仓库数据
        $stockData = $model->getStockDataById($id);
        
        $this->promptParse($stockData);
        
        $areaModel = BaseModel::getInstance(RegionModel::class);
        
        // 获取省级编号
        $areaIdData = $areaModel->getAreaTopIdBySmallId($stockData[SendAddressModel::$addressId_d]);
        
        // 获取省级地区列表
        $areaList = $areaModel->getUpDataAndCache($areaIdData[RegionModel::$parentid_d]);
        
        // 获取市级编号
        $cityId = $areaModel->getUserNameById($stockData[SendAddressModel::$addressId_d], RegionModel::$parentid_d);
        
        // --------------------------------------------------------------
        $this->assign('areaId', $areaIdData[RegionModel::$id_d]);
        
        $this->assign('areaList', $areaList);
        
        $this->assign('stockData', $stockData);
        
        $this->sendModel = SendAddressModel::class;
        
        $this->assign('cityId', $cityId);
        $this->assign('regionModel', RegionModel::class);
        
        $this->display();
    }

    /**
     * 修改保存
     */
    public function saveEdit()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'id',
                'status'
            )
        ), true, array(
            'address_id',
            'address_detail',
            'status'
        )) ?: $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(SendAddressModel::class);
        
        $status = $model->saveEedit($_POST);
        
        $this->promptPjax($status, $model->getError());
        
        $this->ajaxReturnData(array(
            'url' => U('sendGoodsList')
        ));
    }

    /**
     * ajax 获取地区列表
     */
    public function ajaxGetRegionList()
    {
        $validata = array(
            'address_id'
        );
        Tool::checkPost($_POST, array(
            'is_numeric' => $validata
        ), true, $validata) ?: $this->ajaxReturnData(null, 0, '失败');
        
        $areaModel = BaseModel::getInstance(RegionModel::class);
        
        $areaList = $areaModel->getUpDataAndCache($_POST['address_id']);
        
        $this->ajaxReturnData($areaList);
    }

    /**
     * 保存在 数据库
     */
    public function addAddress()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'status'
            )
        ), true, array(
            'address_id',
            'address_detail',
            'status'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(SendAddressModel::class);
        
        $data = $model->getAttribute(array(
            'field' => array(
                SendAddressModel::$id_d
            ),
            'where' => array(
                SendAddressModel::$addressDetail_d => $_POST['address_detail']
            )
        ));
        
        $this->alreadyInDataPjax($data);
        
        $staus = $model->addAddress($_POST);
        
        $this->promptPjax($staus, '操作失败');
        
        $this->ajaxReturnData(array(
            'url' => U('sendGoodsList')
        ));
    }

    /**
     * 删除发货地址
     */
    public function deleteBySendAddress($id)
    {
        ($id = (int) $id) !== 0 ? 　 : $this->ajaxReturnData(null, 0, '操作失败');
        
        $status = BaseModel::getInstance(SendAddressModel::class)->delete($id);
        $this->ajaxReturnData($status);
    }

    /**
     * 快递列表
     */
    public function freightList()
    {
        $this->display();
    }

    /**
     * 添加快递页面
     */
    public function addFreightHTML()
    {
        $model = BaseModel::getInstance(ExpressModel::class);
        
        $colum = $model->buildColumArray([
            ExpressModel::$id_d
        ]);
        $columHTML = $model->getCoulumShowHTML($colum);
        
        $this->assign('express', ExpressModel::class);
        $this->assign('colum', $columHTML);
        
        $this->display();
    }

    /**
     * 添加快递
     */
    public function addExpress()
    {
        Tool::checkPost($_POST, array(), false, [
            'tel',
            'name',
            'url'
        ]) ?: $this->ajaxReturnData(null, 0, '参数错误');
        
        filter_var($_POST['url'], FILTER_VALIDATE_URL) ?: $this->ajaxReturnData(null, 0, 'URL错误');
        
        $regex = [
            'tel' => '/^\d{3,4}-?\d{7,9}$/'
        ];
        
        Tool::connect('ParttenTool', $regex)->validateData($_POST['tel'], 'tel') ?: $this->ajaxReturnData(null, 0, '座机号码错误');
        
        $model = BaseModel::getInstance(ExpressModel::class);
        
        $status = $model->IsExits($_POST['name']);
        
        $this->alreadyInDataPjax($status);
        
        $status = $model->add($_POST);
        
        $this->promptPjax($status, '数据错误');
        
        $this->ajaxReturnData([
            'url' => U('express')
        ]);
    }

    /**
     * 快递公司列表【不允许删除】
     */
    public function express()
    {
        $express = BaseModel::getInstance(ExpressModel::class);
        
        Tool::isSetDefaultValue($_GET, array(
            ExpressModel::$name_d => null
        ));
        
        Tool::connect('ArrayChildren');
        
        $where = $express->buildSearch($_GET, true);
        $data = $express->getDataByPage(array(
            'field' => array(
                ExpressModel::$letter_d
            ),
            'where' => $where,
            'order' => ExpressModel::$id_d . BaseModel::DESC
        ), 15, true);
        
        $this->expressModel = ExpressModel::class;
        
        $this->data = $data;
        
        return $this->display();
    }

    /**
     * 设置是否常用
     */
    public function isCommon()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => 'id'
        ), true, array(
            'id'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        if (! isset($_POST['status']) && ! isset($_POST['order'])) {
            
            $this->ajaxReturnData(null, 0, '参数错误');
        }
        $status = BaseModel::getInstance(ExpressModel::class)->save($_POST);
        $this->updateClient($status, '操作');
    }
}