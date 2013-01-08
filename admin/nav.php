<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('nav');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch($_REQUEST['act'])
{
	case 'list':
		$sql = "SELECT * FROM {$table}nav ORDER BY id";
		$nav = $db->getAll($sql);
		$here = "导航列表";
		$action = array('name'=>'添加导航', 'href'=>'nav.php?act=add');
	    include tpl('list_nav');
	break;

	case 'add':
		$maxorder = $db->getOne("SELECT MAX(navorder) FROM {$table}nav");
		$maxorder = $maxorder + 1;
	    include tpl('add_nav');
	break;

	case 'insert':
		if(empty($_POST['navname']))show("请填写导航名称");
		if(empty($_POST['url']))show("请填写跳转地址");

		$navname   = trim($_POST['navname']);
		$url       = trim($_POST['url']);
		$navorder  = intval($_POST['order']);
		$target    = trim($_POST['target']);
		
		if(empty($navorder)) {
			$sql = "SELECT MAX(navorder) FROM {$table}nav";
			$maxorder  = $db->getOne($sql);
			$navorder = $maxorder + 1;
		}
		$res = $db->query("INSERT INTO {$table}nav (navname,url,target,navorder) VALUES ('$navname','$url','$target','$navorder')");

		clear_caches('phpcache', 'nav');
		admin_log("添加导航 $navname 成功");
		show('添加导航成功','nav.php?act=add');
	break;

	case 'edit':
	    $id = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}nav WHERE id = '$id'";
		$nav = $db->getRow($sql);
		$action = array('name'=>'导航列表', 'href'=>'nav.php?act=list');
		include tpl('edit_nav');
	break;

	case 'update':
		if(empty($_POST['navname']))show("请填写导航名称");
		if(empty($_POST['url']))show("请填写跳转地址");

		$id        = intval($_POST['id']);
		$navname   = trim($_POST['navname']);
		$url       = trim($_POST['url']);
		$navorder  = intval($_POST['order']);
		$target    = trim($_POST['target']);

		if(empty($navorder)) {
			$sql = "SELECT MAX(navorder) FROM {$table}nav";
			$maxorder  = $db->getOne($sql);
			$navorder = $maxorder + 1;
		}

		$res = $db->query("UPDATE {$table}nav SET navname='$navname',url='$url',target='$target',navorder='$navorder' WHERE id = '$id'");
		clear_caches('phpcache', 'nav');
		admin_log("编辑导航 $navname 成功");
		$link = "nav.php?act=list";
		show('编辑导航成功', $link);
	break;

	case 'batch':
		$id = is_array($_REQUEST['id']) ? join(',', $_REQUEST['id']) : intval($_REQUEST['id']);
		if(empty($id))show('没有选择记录');
		$sql = "DELETE FROM {$table}nav WHERE id IN ($id)";
        $re = $db->query($sql);
		clear_caches('phpcache', 'nav');
		admin_log("删除导航 $id 成功");
		show('删除导航成功', 'nav.php?act=list');
	break;
}
?>