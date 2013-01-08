<?php

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

if($_REQUEST['act']=='list')
{
	$sql = "select * from {$table}type where module='help'";
	$res = $db->query($sql);
	$type = array();
	while($row = $db->fetchRow($res)) {
		$row['url'] = url_rewrite('help', array('act'=>'list','tid'=>$row['typeid']));
		$type[] = $row;
	}

	$typeid  = !empty($_REQUEST['typeid']) ? intval($_REQUEST['typeid']) : 0;
	$types = $typeid ? " AND typeid = '$typeid' " : '';
	$page = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
	$size = !empty($_CFG['pagesize']) && intval($_CFG['pagesize']) > 0 ? intval($_CFG['page_size']) : 18;

	$sql = "SELECT COUNT(*) FROM {$table}help WHERE 1 ". $types;
	$count = $db->getOne($sql);
	$max_page = ($count> 0) ? ceil($count / $size) : 1;
	if($page>$max_page)$page = $max_page;
	$pager['search'] = array('act'=>'list','typeid' => $_REQUEST['typeid']);
	$pager = page('help', $typeid, '', $count, $size, $page);

	$sql = "SELECT * FROM {$table}help WHERE 1 " . $types . " ORDER BY listorder,id DESC LIMIT $pager[start],$pager[size]";
	$res = $db->query($sql);
	$helps = array();
	while($row = $db->fetchRow($res)) {
		$row['stitle']   = cut_str($row['title'],'25');
		$row['postdate'] = date('y年m月d日', $row['postdate']);
		$row['url']      = url_rewrite('help',array('hid'=>$row['id'],'act'=>'view'));
		$helps[] = $row;
	}
	$seo['title']   = '网站帮助' . '  - Powered by Phpmps';
	$seo['keywords']  = !empty($keywords) ? $keywords : cut_str($title,'15');
	$seo['description'] = $description;

	include template('help_list');
}
elseif($_REQUEST['act']=='view')
{
	if(isset($_REQUEST['id']))$id = intval($_REQUEST['id']);
	if(empty($id)) {
		header("Location: ./\n");
		exit;
	}

	$res = $db->query("SELECT * FROM {$table}help WHERE id='$id'");
	while($row = $db->fetchRow($res)) {
		$id       = $row['id'];
		$typeid   = $row['typeid'];
		$title    = $row['title'];
		$content  = $row['content'];
		$url      = $row['url'];
		$postdate = date('Y年m月d日', $row['postdate']);
	}
	
	$seo['title']   = $title . '  - Powered by Phpmps';
	$seo['keywords']  = !empty($keywords) ? $keywords : cut_str($title,'15');
	$seo['description'] = $description;

	include template('help');
}
?>