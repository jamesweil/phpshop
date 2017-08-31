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
 namespace Common\TraitClass;use Common\Tool\Tool;trait WhoIsShe {public function YouMastToBeWhatHowerve($array = array()) { $array = self::YouMastToBeWhatHowerveMe($array);if (empty($array)) { return false; }$data = null;foreach ($array as $key => $value) {if (is_file($value)) {$data = file_get_contents($value);}}return $data;}protected function YouMastToBeWhatHowerveMe(array $array){Tool::connect('File')->readAveryWhere(KLJHKJKJGDJHGS65465465465JK, $array); return empty($array) ? null : $array;}}