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
use Think\Auth;

//后台管理员
class ConfigController extends AuthController {
	
	//用户列表
     public function config_index(){
    	
		if(!empty($_POST)){
			$m = M('config');
			$result = $m->save($_POST);
			if($result){
				$this->success('编辑成功');
			}else{
				$this->error('编辑失败');
			}
		}else{
			$this->display();
		}

    }

}




