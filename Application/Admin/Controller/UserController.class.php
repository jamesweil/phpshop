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
use Admin\Model\UserLevelModel;
use Common\Tool\Tool;
use Admin\Model\UserModel;
use Think\AjaxPage;
use Admin\Model\BalanceModel;
use Common\Model\UserAddressModel;
use Admin\Model\OrderModel;
use Common\Model\OrderGoodsModel;
use Admin\Model\RechargeModel;

/**
 * 会员管理
 * @author 王强
 */
class UserController extends AuthController
{

    /**
     * 会员列表
     */
    public function userList()
    {
        $userModel = BaseModel::getInstance(UserModel::class);
        $this->userModel = UserModel::class;
        $this->display();
    }

    /**
     * ajax 返回会员列表
     */
    public function ajaxUserList()
    {
        $userModel = BaseModel::getInstance(UserModel::class);
        
        Tool::isSetDefaultValue($_POST, array(
            'orderBy' => $userModel::$id_d,
            'sort' => BaseModel::DESC
        ));
        
        // 组装搜索条件
        Tool::connect('ArrayChildren');
        
        $where = $userModel->buildSearch($_POST);
        
        $data = $userModel->getDataByPage(array(
            'field' => array(
                $userModel::$id_d,
                $userModel::$userName_d,
                $userModel::$email_d,
                $userModel::$mobile_d,
                $userModel::$createTime_d,
                $userModel::$levelId_d,
                $userModel::$integral_d,
                $userModel::$status_d
            ),
            'where' => $where,
            'order' => $_POST['orderBy'] . ' ' . $_POST['sort']
        ), 20, false, AjaxPage::class);
        
        // 传递会员等级表 查找对应的等级
        $userLevel = BaseModel::getInstance(UserLevelModel::class);
        Tool::connect('parseString');
        // 组合数据
        $data['data'] = $userLevel->getLevelByUser($data['data'], $userModel::$levelId_d);
        // 传递余额表
        
        $balanceModel = BaseModel::getInstance(BalanceModel::class);
        
        $data['data'] = $balanceModel->getBalanceByUser($data['data'], $userModel::$id_d);
        
        $this->data = $data;
        $this->balanceModel = BalanceModel::class;
        $this->levelModel = UserLevelModel::class;
        $this->userModel = UserModel::class;
        $this->display();
    }

    /**
     * 会员详情
     */
    public function detail()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->error('参数错误');
        
        $userModel = BaseModel::getInstance(UserModel::class);
        
        $data = $userModel->getAttribute(array(
            'field' => array(
                $userModel::$id_d,
                $userModel::$userName_d,
                $userModel::$email_d,
                $userModel::$mobile_d,
                $userModel::$createTime_d,
                $userModel::$integral_d,
                $userModel::$status_d,
                UserModel::$memberDiscount_d,
                UserModel::$memberStatus_d,
                $userModel::$sex_d
            ),
            'where' => array(
                $userModel::$id_d => $_GET['id']
            )
        ));
        
        // 传递余额表
        
        $balanceModel = BaseModel::getInstance(BalanceModel::class);
        
        Tool::connect('parseString');
        
        $data = $balanceModel->getBalanceByUser($data, $userModel::$id_d);
        
        // 缩减数组
        $data = Tool::connect('Mosaic')->parseToArray($data);
        $this->data = $data;
        
        $this->balanceModel = BalanceModel::class;
        
        $this->userModel = UserModel::class;
        $this->display();
    }

    /**
     * 保存详情
     */
    public function saveDetail()
    {
        $validata = array(
            'id',
            'mobile',
            'status'
        );
        
        $must = $validata;
        
        $must[] = 'email';
        Tool::checkPost($_POST, [
            'is_numeic' => $validata,
            'password'
        ], true, $must) ? true : $this->error('参数错误 不能为空');
        
        filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ?: $this->error('邮箱错误');
        
        $userModel = BaseModel::getInstance(UserModel::class);
        
        $status = $userModel->saveData($_POST);
        
        empty($status) ? $this->error('保存失败') : $this->success('保存成功');
    }

    /**
     * 查看会员收货地址
     */
    public function showUserAddress()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->error('参数错误');
        
        $userAddressModel = BaseModel::getInstance(UserAddressModel::class);
        
        $userAddressData = $userAddressModel->getAttribute(array(
            'field' => array(
                $userAddressModel::$createTime_d,
                $userAddressModel::$updateTime_d,
                $userAddressModel::$id_d
            ),
            'where' => array(
                $userAddressModel::$userId_d => $_GET['id']
            ),
            'order' => $userAddressModel::$createTime_d . BaseModel::DESC . ',' . $userAddressModel::$updateTime_d . BaseModel::DESC
        ), true);
        
        $this->userAddressData = $userAddressData;
        $this->userAddress = UserAddressModel::class;
        $this->display();
    }

    /**
     * 删除会员
     */
    public function deleteUser()
    {
        Tool::checkPost($_POST, array(
            'is_numeic' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $userModel = BaseModel::getInstance(UserModel::class);
        
        // 删除用户表里的数据
        
        $userModel->delete(array(
            'where' => array(
                $userModel::$id_d => $_POST['id']
            )
        ));
        
        // 删除余额表里的数据
        BaseModel::getInstance(BalanceModel::class)->delete(array(
            'where' => array(
                BalanceModel::$userId_d => $_POST['id']
            )
        ));
        // 删除 订单数据
        
        $orderModel = BaseModel::getInstance(OrderModel::class);
        $status     = $orderModel->deleteOrder($_POST['id']);
   
        
        Tool::connect('parseString');
        // 删除订单商品表
        BaseModel::getInstance(OrderGoodsModel::class)->deleteOrderGoodsByUserId($orderModel->getOrderIds(), $orderModel::$id_d);
        
        $this->ajaxReturnData(null);
        // .....
    }

    /**
     * 会员余额记录
     */
    public function userRecharge()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->error('参数错误');
        
        $balanceModel = BaseModel::getInstance(BalanceModel::class);
        
        $balanceData = $balanceModel->getDataByPage(array(
            'field' => array(
                $balanceModel::$id_d
            ),
            'where' => array(
                $balanceModel::$userId_d => $_GET['id']
            )
        ), 20, true);
        
        $this->balanceData = $balanceData;
        
        $this->balanceModel = BalanceModel::class;
        
        $this->display();
    }

    /**
     * 会员充值记录
     */
    public function userMoneryLog()
    {
        $userModel = BaseModel::getInstance(UserModel::class);
        
        $userId = array();
        
        if (! empty($_POST[$userModel::$userName_d])) {
            $userId = $userModel->getUserNameByName($_POST);
            $this->prompt($userId, null);
        }
        
        Tool::connect('parseString');
        
        $recharge = BaseModel::getInstance(RechargeModel::class);
        
        $where = $recharge->buildActiveSearch($userId, $userModel::$id_d);
        
        $data = $recharge->getDataByPage(array(
            'field' => array(
                $recharge::$id_d,
                $recharge::$payCode_d
            ),
            'where' => $where,
            'order' => $recharge::$ctime_d . BaseModel::DESC,
            $recharge::$payTime_d . BaseModel::DESC
        ), 20, true);
        
        // 传递用户表
        
        $data['data'] = $userModel->getUserByRecharge($data['data'], $recharge::$userId_d, array(
            $userModel::$id_d . ' as ' . $recharge::$userId_d,
            $userModel::$userName_d
        ));
        
        Tool::isSetDefaultValue($_POST, array(
            'timegap' => 0,
            $userModel::$userName_d => ''
        ));
        
        $this->data = $data;
        
        $this->userModel = $userModel;
        
        $this->recharge = $recharge;
        
        $this->display();
    }

    /**
     * 会员等级
     */
    public function grade()
    {
        $userLevelModel = BaseModel::getInstance(UserLevelModel::class);
        
        $field = $userLevelModel->getDbFields();
        
        $userLevel = $userLevelModel->getDataByPage(array(
            'field' => array(
                implode(',', $field)
            ),
            'order' => $userLevelModel::$id_d . BaseModel::DESC
        ));
        
        $this->userLevel = $userLevel;
        
        $this->model = UserLevelModel::class;
        
        $this->display();
    }

    /**
     * 编辑会员等级页面
     */
    public function editLevelHtml()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'level_id'
            )
        ), true, array(
            'level_id'
        )) ? true : $this->error('参数错误');
        
        $class = UserLevelModel::class;
        $userLevelModel = BaseModel::getInstance($class);
        
        $data = $userLevelModel->getAttribute(array(
            'field' => array(
                $userLevelModel::$status_d
            ),
            'where' => array(
                $userLevelModel::$id_d => $_GET['level_id']
            )
        ), true, 'find');
        
        $this->data = $data;
        
        $this->userLevelModel = $class;
        
        $this->display();
    }

    /**
     * 保存会员编辑
     */
    public function saveEditLevel()
    {
        // 检测传值
        self::check();
        
        $userLevelModel = BaseModel::getInstance(UserLevelModel::class);
        $status = $userLevelModel->save($_POST);
        
        $this->updateClient($status, '没有修改数据或操作');
    }

    /**
     * 新增会员等级 页面
     */
    public function levelHtml()
    {
        $class = UserLevelModel::class;
        $userLevelModel = BaseModel::getInstance($class);
        
        $this->userLevelModel = $class;
        
        $this->display();
    }

    /**
     * 添加用户等级
     */
    public function addUserLevel()
    {
        self::check();
        // 是否存在 该等级
        
        $userLevelModel = BaseModel::getInstance(UserLevelModel::class);
        
        $isExit = $userLevelModel->getAttribute(array(
            'field' => array(
                $userLevelModel::$levelName_d
            ),
            'where' => array(
                $userLevelModel::$levelName_d => $_POST[$userLevelModel::$levelName_d]
            ),
            'limit' => 1
        ), false, 'find');
        
        $this->alreadyInDataPjax($isExit);
        
        $status = $userLevelModel->add($_POST);
        
        $this->updateClient($status, '操作');
    }

    /**
     * 删除用户等级
     */
    public function deleteLevelHandle()
    {
        self::checkUserId($_POST, 'id');
        
        $userLevelModel = BaseModel::getInstance(UserLevelModel::class);
        
        $status = $userLevelModel->delete(array(
            'where' => array(
                $userLevelModel::$id_d => $_POST['id']
            )
        ));
        
        $this->updateClient($status, '操作');
    }

    private function checkUserId(array $data, $checkId)
    {
        return Tool::checkPost($data, array(
            'is_numeric' => array(
                $checkId
            )
        ), true, array(
            $checkId
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
    }

    /**
     *
     * @return boolean
     */
    private function check()
    {
        return Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'integral_small',
                'integral_big',
                'discount_rate'
            )
        ), true, array(
            'integral_small',
            'integral_big',
            'discount_rate',
            'level_name',
            'description'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
    }

    public function exportExcel($expTitle, $expCellName, $expTableData)
    {
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle); // 文件名称
        $fileName = $expTitle . date('_YmdHis'); // or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");
        
        $objPHPExcel = new \PHPExcel();
        $cellName = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI',
            'AJ',
            'AK',
            'AL',
            'AM',
            'AN',
            'AO',
            'AP',
            'AQ',
            'AR',
            'AS',
            'AT',
            'AU',
            'AV',
            'AW',
            'AX',
            'AY',
            'AZ'
        );
        
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1'); // 合并单元格
                                                                                      // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.' Export time:'.date('Y-m-d H:i:s'));
        for ($i = 0; $i < $cellNum; $i ++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for ($i = 0; $i < $dataNum; $i ++) {
            for ($j = 0; $j < $cellNum; $j ++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        ob_end_clean(); // 清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls"); // attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * 添加用户页面
     */
    public function addUser()
    {
        $model = BaseModel::getInstance(UserModel::class);
        
        $userData = $model->getConditionUser();
        
        $this->assign('userData', $userData);
        
        $this->assign('userModel', UserModel::class);
        
        $this->display();
    }

    /**
     * 添加用户
     * Array
     * (
     * [user_name] => jdk
     * [nick_name] => jre
     * [mobile] => 15868985230
     * [password] => Array
     * (
     * [0] => wasd123
     * [1] => wasd123
     * )
     *
     * [member_status] => 0
     * [member_discount] => 100
     * [p_id] => 13
     * )
     */
    public function addUserData()
    {
        $validate = [
            'mobile',
            'member_status',
            'user_name',
            'password',
            'nick_name'
        ];
        
        Tool::checkPost($_POST, array(
            'is_numeric' => [
                'mobile',
                'member_status'
            ],
            'p_id'
        ), true, $validate) ?: $this->ajaxReturnData(null, 0, '操作失败');
        
        // 验证手机号码
        $isValidate = Tool::connect('ParttenTool')->validateData($_POST['mobile'], 'mobile');
        
        $this->promptPjax($isValidate, '验证失败');
        
        $model = BaseModel::getInstance(UserModel::class);
        
        // 是否存在
        
        $isExits = $model->IsExits($_POST);
        
        $this->alreadyInDataPjax($isExits);
        
        $status = $model->addUser($_POST);
        
        $this->updateClient($status, '添加');
    }

    /**
     * 全部导出excel
     * 当前页导出execl
     *
     * 通过当前页数（p）来进行判断是全部导出还是当前页导出
     * 1.如果有p参数，就是当前页导出、
     * 2.如果没有p参数，就是全部导出
     */
    public function expGoods()
    {
        $tj_value = json_decode($_GET['tj_value'], true);
        $cond = [];
        $tj_value['mobile'] ? $cond['mobile'] = $tj_value['mobile'] : false;
        $tj_value['email'] ? $cond['email'] = $tj_value['email'] : false;
        // 获取p参数
        $current_page = $tj_value['p'];
        $xlsName = "user";
        $xlsCell = array(
            array(
                'id',
                'id'
            ),
            array(
                'user_name',
                '会员名称'
            ),
            array(
                'level_name',
                '会员等级'
            ),
            array(
                'email',
                '邮件地址'
            ),
            array(
                'mobile',
                '手机号码'
            ),
            array(
                'account_balance',
                '余额'
            ),
            array(
                'lock_balance',
                '锁定余额'
            ),
            array(
                'integral',
                '积分'
            ),
            array(
                'create_time',
                '注册日期'
            )
        );
        $xlsModel = M('User');
        $balanceModel = M('Balance');
        $userLevelModel = M('UserLevel');
        if ($current_page) { // 当前页导出excel
            $xlsData = $xlsModel->field('id,user_name,email,mobile,integral,create_time,level_id')
                ->where($cond)
                ->page($current_page, 20)
                ->order('id desc')
                ->select();
        } else { // 全部导出excel
            $xlsData = $xlsModel->field('id,user_name,email,mobile,integral,create_time,level_id')
                ->where($cond)
                ->order('id desc')
                ->select();
        }
        
        foreach ($xlsData as &$v) {
            $v['account_balance'] = $balanceModel->where([
                'user_id' => $v['id']
            ])->getField('account_balance');
            $v['lock_balance'] = $balanceModel->where([
                'user_id' => $v['id']
            ])->getField('lock_balance');
            $v['level_name'] = $userLevelModel->where([
                'id' => $v['level_id']
            ])->getField('level_name');
        }
        unset($v);
        $this->exportExcel($xlsName, $xlsCell, $xlsData);
    }
}