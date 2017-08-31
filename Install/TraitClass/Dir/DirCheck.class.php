<?php
namespace TraitClass\Dir;

trait DirCheck 
{

    protected $siteDir = null;

    protected $str;

    protected $isWrite = [];

    public static function checkDir(array $dir)
    {
        if (empty($dir)) {
            return null;
        }
        
        $str = null;
        
        foreach ($dir as $value) {
            
        }
    }

    /**
     * 路径处理
     */
    public static function dirPath($path)
    {
        $path = str_replace('\\', '/', $path);
        if (substr($path, - 1) != '/')
            $path = $path . '/';
        return $path;
    }

    /**
     * 创建路径
     */
    public static function dirCreate($path, $mode = 0777)
    {
        if (is_dir($path))
            return TRUE;
        $ftp_enable = 0;
        $path = self::dirPath($path);
        $temp = explode('/', $path);
        $curDir = '';
        $max = count($temp) - 1;
        for ($i = 0; $i < $max; $i ++) {
            $curDir .= $temp[$i] . '/';
            if (is_dir($curDir))
                continue;
            mkdir($curDir, 0777, true);
            chmod($curDir, 0777);
        }
        return is_dir($path);
    }
    
    /**
     * 测试是否可写
     */
    public static function testWrite($dir) 
    {
        $tFile = "_wq.txt";
       
        $fp = fopen($dir . '/' . $tFile, "w");
        
        if (!$fp) {
            return false;
        }
        fclose($fp);
        $rs = unlink($dir .  '/' . $tFile);
       
        if ($rs) {
            return true;
        }
        
        return false;
    }
}