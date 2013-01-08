<?php

/*
 * $Id: common.php 2008-12-25 9:12:02z happyboy $
 * -------------------------------------------
 * 网址：www.phpmps.com
 * -------------------------------------------
 * 这是一个自由软件。
*/

if (!defined('IN_PHPMPS'))
{
    die('Access Denied');
}

//定义错误级别
error_reporting(E_ERROR | E_WARNING | E_PARSE);

/* 取得根目录 */
define('PHPMPS_ROOT', str_replace("\\", '/', substr(dirname(__FILE__), 0, -7)));

set_magic_quotes_runtime(0);

@set_time_limit(360);

require_once PHPMPS_ROOT . 'install/global.fun.php';
require_once PHPMPS_ROOT . 'include/version.inc.php';
require_once PHPMPS_ROOT . 'include/json.class.php';

//转义处理客户端提交的数据
if(!get_magic_quotes_gpc())
{
	$_POST   = addslashes_deep($_POST);
	$_GET    = addslashes_deep($_GET);
	$_COOKIE = addslashes_deep($_COOKIE);
}

header('Content-type: text/html; charset='.$charset);
?>