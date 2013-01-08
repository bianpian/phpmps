<?php

/*
 * $Id: dirs.php 2008-8-1 9:12:02z happyboy $
 * -------------------------------------------
 * 网址：www.phpmps.com
 * -------------------------------------------
 * 这是一个自由软件。
*/

if (!defined('IN_PHPMPS'))
{
    die('Access Denied');
}

//需要检测是否可写的文件数组,要保证其目录和子目录可写
$dirs = array(
	'data',
	'data/config.php',
	'data/flashimage',
	'data/infoimage',
	'data/upload',
	'data/upload/Image',
	'data/upload/File',
	'data/upload/Flash',
	'data/upload/Media',
	'data/bakup',
	'data/ads',
	'data/phpcache',
	'data/sqlcache',
	'data/compiled',
	'data/com',
	'data/com/thumb',
	'sitemap.xml',
	'uc_client/data',
	'uc_client/data/cache'
	);
?>