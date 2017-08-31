<?php
namespace Upload\Controller;

use Common\Controller\AuthController;
use Think\Controller;
use Common\Tool\Tool;
use Common\Model\FileUploadModel;
use Common\Tool\Event;
use Common\Tool\Extend\Session;
use Common\Tool\Extend\UnlinkPicture;

class UploadController extends AuthController
{
   
    
    public function __construct()
    {
       
        if (!empty($_GET['sId'])) {
            $sId = base64_decode($_GET['sId']);
            Event::insetListen('sId', function (&$param)use($sId){
                $param = $sId;
            });
            (new Session())->setSession('*');
        }
        parent::__construct();
        
        Tool::isSetDefaultValue($_GET, array('uploadNum' => 1));
    }
    
    public function index()
    {
        Tool::checkPost($_GET, (array)null, false, array('uploadNum', 'input')) ? true : $this->error('参数错误');

        Tool::isSetDefaultValue($_GET, array('path' => 'brand'));
        
        //获取图片宽高设置
        
        $widthAndHeight = $this->getImageWidthAndHeight(C($_GET['config']));
        
        //保存在session中 上传时检测 
        
        $_GET = array_merge($_GET, $widthAndHeight);
        
        $_GET['size'] = (C('GOODS_UPLOAD.maxSize')/1024/1024).'M';
        $_GET['type'] = C('GOODS_UPLOAD.exts');
       
        $this->info = $_GET;
        $this->assign('sId', base64_encode(session_id($_SESSION['aid'])));
        $this->display();
    }
    
    /**
     * 上传图片 一张【远程服务器】
     */
    public function  uploadImage()
    {
        Tool::checkPost($_FILES, (array)null, false, array('Filedata')) ? true : $this->ajaxReturnData(null, '400', '参数错误');
        
        Tool::connect('Mosaic');
        $file = Tool::array_depth($_FILES) ===2 ? Tool::parseToArray($_FILES) : $file;
       
        Tool::connect('CURL');
        $response = Tool::uploadFile($file, C('IMAGE_UPLOAD_SERVER'), 1);
        $response = json_decode($response,true);
       
        $this->updateClient(C('IMG_DOMAIN').$response['data'], '添加');
    }
    /**
     * 本地 
     */
    public function  uploadImageToLocal()
    {
        
         $validata = [
             'min_height',
             'min_width',
             'max_height',
             'max_width'
         ];
        
         Tool::checkPost($_GET, array('is_numeric' => $validata), false, $validata) ? true : $this->ajaxReturnData(null, 0, '图片宽高度参数错误');
         
         Tool::checkPost($_FILES,  array(), false, array('Filedata')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
         $_FILES =  Tool::connect('File')->parseFile($_FILES);
         
         //转换一维数组
         Tool::connect('Mosaic');  
         $file = FileUploadModel::getInitnation();
         //$filePath = $userHeader->UploadFile(C('GOODS_UPLOAD.rootPath','./Uploads/goods/'));

        //你多传一个参数 例如 path  post  或者 get
        // 这是设置 默认值

        //这里是设置值 不返回值
//
         C('GOODS_UPLOAD.rootPath', './Uploads/'.$_GET['path'].'/');
        
         $file->setWidthAndHeightConfig($_GET);
         
         $filePath = $file->UploadFile(C('GOODS_UPLOAD'));
         $this->updateClient($filePath, $file->getError());
    }
    
    /**
     * 多张图片上传 
     */
    public function uploadManyIamge()
    {
        // 这个是 打印信息的 比 dump 要好一些
        showData($_FILES, 1);
        Tool::checkPost($_FILES, (array)null, false, array('detail')) ? true : $this->ajaxReturnData(null, '400', '参数错误');
    }
    
    
    /**
     * 删除文件
     */
    public function deleteFile()
    {
        Tool::checkPost($_GET, (array)null, false, array('filename')) ? true : $this->ajaxReturnData(null, '400', '参数错误');
        
        //添加删除缩略图监听
        Event::insetListen('thumbImage', function (array & $param){
            
            if (empty($param)) {
                return false;
            }
            $thumb =  $tmp = null;
            foreach ($param as  $value) {
               
                $tmp = substr($value, strrpos($value, '/')+1);
                
                $thumb = 'thumb_'.$tmp;
                $value = './'.str_replace($tmp, $thumb, $value);
               
                if (!is_file($value)) {
                    continue;
                }
                unlink($value);
            }
        });
        
        $status = Tool::partten(array($_GET['filename']), UnlinkPicture::class);
        $this->updateClient($status, '删除，');
    }
    
}