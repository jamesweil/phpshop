<?php
namespace Home\Controller;

use Think\Controller;
use TraitClass\Dir\DirCheck;
use TraitClass\Db\checkDbTrait;
use Think\Exception;

// 安装模块
class IndexController extends Controller
{
    use DirCheck;
    use checkDbTrait;
    // 首页
    public function index()
    {
        $this->display();
    }

    public function stepTwo()
    {
        // 获取gd信息
        $gd = gd_info();
        
        $err = 0;
        if (empty($gd['GD Version'])) {
            $gd = '<font color=red>[×]Off</font>';
            $err ++;
        } else {
            $gd = '<font color=green>[√]On</font> ' . $gd['GD Version'];
        }
        // new \PDO('mysql:localhost:3306;dbname=ysbg', 'root', 'yisu123');
        if (class_exists('PDO')) {
            $mysql = '<span class="correct_span">&radic;</span> 已安装';
        } else {
            $mysql = '<span class="correct_span error_span">&radic;</span> 请安装PDO扩展';
            $err ++;
        }
        if (ini_get('file_uploads')) {
            $uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
        } else {
            $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
        }
        if (function_exists('session_start')) {
            $session = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $session = '<span class="correct_span error_span">&radic;</span> 不支持';
            $err ++;
        }
        if (function_exists('curl_init')) {
            $curl = '<font color=green>[√]支持</font> ';
        } else {
            $curl = '<font color=red>[×]不支持</font>';
            $err ++;
        }
        if (function_exists('file_put_contents')) {
            $filePutContents = '<font color=green>[√]支持</font> ';
        } else {
            $filePutContents = '<font color=red>[×]不支持</font>';
            $err ++;
        }
        
        $name = $_SERVER["SERVER_NAME"];
        
        $this->siteDir = self::dirPath(substr(dirname(__FILE__), 0, - 8)); // 设置站点路径
        
        $isWrite = C('is_write_dir');
        
        $max_execution_time = ini_get('max_execution_time');
        
        $safeMode = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');
        
        $this->curl = $curl;
        
        $this->session = $session;
        
        $this->mysql = $mysql;
        
        $this->filePutContents = $filePutContents;
        
        $this->error = $err;
        
        $this->cObj = __CLASS__;
        
        $this->assign('siteDir', $this->siteDir);
        
        $this->assign('isWrite', $isWrite);
        
        $this->gd = $gd;
        $this->safeMode = $safeMode;
        $this->uploadSize = $uploadSize;
        $this->display();
    }

    /**
     * 第三步
     */
    public function stepThree()
    {
        $this->display();
    }

    /**
     * 数据库链接检测
     */
    public function checkLink()
    {
        $json = [
            'status' => 2
        ];
        if (empty($_POST)) {
            die(json_encode($json));
        }
        $status = $this->checkDb($_POST);
        
        $json['status'] = $status;
        // showData(self::$errorDb, 1);
        die(json_encode($json));
    }

    /**
     * 第四部
     */
    public function stepFour()
    {
        $this->display();
    }

    public function installDb()
    {
        ignore_user_abort(true);
        ini_set('max_execution_time',500);
        
        $json = [
            'msg' => '参数错误'
        ];
     
        if (!is_numeric($_GET['n'])) {
            die(json_encode($json));
        }
        if (! class_exists('PDO')) {
            $arr['msg'] = "请安装 PDO 扩展!";
            echo json_encode($json);
            exit();
        }
        
        $n = intval($_GET['n']);
        
        $dbHost = trim($_POST['dbhost']);
        
        $_POST['dbport'] = $_POST['dbport'] ? $_POST['dbport'] : '3306';
        $dbName = strtolower(trim($_POST['dbname']));
        
        $dbUser = trim($_POST['dbuser']);
        $dbPwd = trim($_POST['dbpw']);
        
        $pdo = $this->getPdoObj($_POST);
       
        
        $this->msg($dbName);
        
        $dbPrefix = empty($_POST['dbprefix']) ? 'db_' : trim($_POST['dbprefix']);
      
        
        if (!$pdo->query('use `'.$dbName.'`')) {
            if ($n == - 1) {
                $json['n'] = 0;
                $json['msg'] = "成功创建数据库:{$dbName}<br>";
                echo json_encode($json);
                exit();
            }
            $pdo->query('use `'.$dbName.'`');
        }
       
        // 读取出所有行
        $lines = file(C('file'));
   
        $sqlstr = null;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line != "" && ! ($line{0} == "#" || $line{0} . $line{1} == "--" || $line{0} . $line{1} . $line{1} == '/*')) {
                $sqlstr .= $line . '|';
            }
        }
        $sqls = explode("|", $sqlstr);
        
        $sqldata = implode("\n", $sqls);
        
        $sqlFormat = self::sqlSplit($sqldata, $dbPrefix);
        
        //执行SQL
        $this->execSql($sqlFormat, $n);
        
            // 读取配置文件，并替换真实配置数据1
        $strConfig = file_get_contents(C('config'));
        $strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
        $strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
        $strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
        $strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
        $strConfig = str_replace('#DB_PORT#', $_POST['dbport'], $strConfig);
        $strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
        $strConfig = str_replace('#DB_CHARSET#', 'utf8', $strConfig);
        $strConfig = str_replace('#DB_DEBUG#', false, $strConfig);
        
        $config = C('installed_config');
        
        chmod($config, 0777); // 数据库配置文件的地址
        file_put_contents($config, $strConfig); // 数据库配置文件的地址                                                                               
       
        $this->insertAdmin($dbPrefix);
        
        exit();
    }
    
    /**
     * 第五步
     */
    public function stepFive()
    {
        $putFile = C('put_file');
        (fopen($putFile, 'r'));
        chmod($putFile, 0777); 
        file_put_contents($putFile, '已安装');
        $this->display();
    }
}





