<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强,张蔡青 <opjklu@126.com>
// +----------------------------------------------------------------------

namespace Home\Controller;

use Common\Controller\ProductController;

class CheckGRantController
{

    private $controllrr;
    /**
     * 传递的参数
     * @var mixed
     */
    private $data;
    private $time;

    public function __construct($data)
    {
        $this->data = $data;

        $this->time = time();

        $this->controllrr = new ProductController();

    }

    public function check()
    {
        header("Access-Control-Allow-Origin:*" );
        $domain_name = $_POST['url'];
        if( !filter_var('HTTP://'.$domain_name, FILTER_VALIDATE_URL)){
            $this->controllrr->ajaxReturnData([],2,'参数错误');//域名格式不对
        }


        if(!($info = $this->CheckDomainName($domain_name))){
            $this->controllrr->ajaxReturnData([],2,'未授权');

        }
        if(strtotime($info['end_time']) > $this->time){
            $this->controllrr->ajaxReturnData([],1,'授权成功');
        }
        $this->controllrr->ajaxReturnData([],3,'授权到期');

    }

    public function CheckDomainName($domain_name)
    {
        return M('domain_name')->where(['domain_name' => $domain_name])->find();
    }
}
























