<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
 
// 定义应用目录
define('APP_PATH', __DIR__ . '/./application/');

// 定义网站根路径
define('BASE_PATH', substr($_SERVER['SCRIPT_NAME'],0,-10));

// 加载框架引导文件
require __DIR__ . '/./thinkphp/start.php';
