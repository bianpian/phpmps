<?php
define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';

//$count = $db->getOne("select count(*) from {$table}info where is_check=1");
//$today_count = $db->getOne("select count(*) from {$table}info where is_check=1 and postdate>".mktime(0,0,0));
$sql = "select catid,count(*) as num from {$table}info where is_check=1 group by catid ";
$counts = $db->getAll($sql);
$info_count = array();
foreach($counts as $k=>$v) { $info_count[$v['catid']] = $v['num']; }

$flash = get_flash();//焦点图
$fac   = get_fac('20');//便民信息
$links = get_link_list();//友情链接
$helps = get_index_help('5');//首页帮助
$coms  = get_index_com('7');//首页黄叶

$articles   = get_index_article('7');//文章
$comments   = get_new_comment('6');//最新评论信息
$new_info   = get_info('','','10','','date','10');//最新信息
$pro_info   = get_info('','','10','pro','','10');//推荐信息
$hot_info   = get_info('','','10','','click', '10', '','m-d');//热门信息
$thumb_info = get_info('','','7','','date','9','1');//图片信息

$seo['title'] = $CFG['webname'] . ' - Powered by Phpmps';
$seo['keywords'] = $CFG['keywords'];
$seo['description'] = $CFG['description'];

include template('index');
?>