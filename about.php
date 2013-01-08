<?php

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';

if(isset($_REQUEST['id']))$id = intval($_REQUEST['id']);
if(empty($id)) showmsg('缺少参数！');

$about_info = $db->getRow("select * from {$table}about where id='$id'");
if(empty($about_info))showmsg('信息不存在', 'index.php');
extract($about_info);
$postdate = date('Y年m月d日', $postdate);

if(!empty($url)) {
	header("Location: $url");
	exit;
}

$res = $db->query("select * from {$table}about order by id");
$abouts = array();
while($row = $db->fetchRow($res)) {
	$row['title'] = cut_str($row['title'], '20');
	$row['url'] = url_rewrite('about', array('aid'=>$row['id']));
	$abouts[] = $row;
}

$seo['title'] = $title . ' - Powered by Phpmps';
$seo['keywords'] = $keywords;
$seo['description'] = $description? $description: cut_str($content,'30');

include template('about');
?>