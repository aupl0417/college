<?php

/*
 * Version : 2.5.0
 * Author  : flybug
 * Comment : 2015-10-19
 * 
 * var:
 * 	model-调用的功能模
 * memo:
 * 	根据相关参数调用模块，配合不同的调用参数来定制
 */
define('FRAME_NAME', 'flybug'); //框架名称
define('FRAME_VERSION', '2.0'); //框架版本
define('FRAMEROOT', WEBROOT . '/frame'); //框架的根路径
define('PUBLICROOT', WEBROOT . '/app/public'); //公共项目的路径
define('APPROOT', WEBROOT . '/app/' . APP_NAME); //应用根目录
require(WEBROOT . '/frame/sys/regist.php'); //加载框架核心注册类
require(FRAMEROOT . "/class/class_cache.php"); //加载缓存类
require(FRAMEROOT . "/class/class_function.php" ); //基础函数
require(APPROOT . '/config.php'); //加载应用配置文件
require(APPROOT . '/regist.php'); //加载app注册类
require(APPROOT . "/language/" . LANGUAGE . ".php"); //语言
date_default_timezone_set('PRC'); //时间区域（中国）
spl_autoload_register(['F', 'myAutoload']);

//处理提交数据
$options = packpara::packValue();
define('PATH_TEMPLATE', APPROOT . '/template/' . LANGUAGE . '/' . $options['PATH_MODEL']);

//模版替换宏
define('_TEMP_PUBLIC_', '/app/public/assets');
define('_TEMP_SHARE_', '/app/' . APP_NAME . '/template/' . LANGUAGE . '/share');
define('_TEMP_ACTION_', '/app/' . APP_NAME . '/template/' . LANGUAGE . '/' . $options['PATH_MODEL']);
define('_TEMP_UPLOAD_', '/app/' . APP_NAME . '/upload');
define('_TEMP_DOWNLOAD_', '/app/' . APP_NAME . '/download');
define('_TEMP_CACHE_', '/app/' . APP_NAME . '/cache');

//用于权限校验的字符串
define('POWER_CHECK', 'DTTX@123');

$action = APPROOT . '/model/' . $options['PATH_MODEL'] . '/' . $options['PATH_ACTION'] . '.php';

if (file_exists($action)) {
    require (APPROOT . '/model/' . $options['PATH_MODEL'] . '/' . $options['PATH_ACTION'] . '.php');
    //系统功能开
    error_reporting(E_ALL ^ E_NOTICE);
    if (!DEBUG) {
        //error_reporting(0);
    } else {
        error_reporting(E_ALL);
    }

    # 初始化session.
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', DOMAIN); //
    ini_set('session.cookie_lifetime', '86400');
    session_start();



    $act = str_replace('.', '_', $options['PATH_ACTION']);
    //模块调用
    $obj = new $act($options);
    $obj->run();
} else {
	if(F::isMobile()) 
	   header('location:http://' . WAPURL .'/');
	else
	   header('location:http://' . WWWURL .'/404.html');
	  
	


}

//后期处理
//ob_end_flush();
