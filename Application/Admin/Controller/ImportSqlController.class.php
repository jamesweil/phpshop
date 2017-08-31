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
use Think\Upload;

class ImportSqlController extends AuthController
{
    public function add(){
        $this->display("add");
    }

    /**
     * 上传sql文件
     */
    public function uploadSql(){
        $options = [
            'rootPath'=>'./Uploads/sql/',
        ];

        $upload    = new Upload($options);

        $file_info = $upload->uploadOne($_FILES['file_data']);

        $file_url = __SERVER__."/Uploads/sql/".$file_info['savepath'].$file_info["savename"];

        $num = strripos($file_url,'.');   //最后一次出现的位置
        $suffix = substr($file_url,$num+1);    //截取获得最后参数值
        if($suffix != "sql"){
            echo "你上传的不是sql文件，请上传正确的sql文件";
            exit;
        }

        $url = "http://www.ysbg.com/Uploads/sql/2016-12-29/5864d616b6ee0.sql";
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $contents = curl_exec($ch);
        curl_close($ch);

        $_arr = explode(';', $contents);

        //执行sql语句
        if($_arr){
            foreach ($_arr as $_value) {

                M()->execute($_value.';');
            }
            echo "执行成功";
            exit;
        }else{
            echo "执行失败";
            exit;
        }








    }
}