<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('link');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch($_REQUEST['act'])
{
	case 'list':
		$sql = "SELECT * FROM {$table}link ORDER BY id";
		$links = $db->getAll($sql);
		$here = "链接列表";
		$action = array('name'=>'添加链接', 'href'=>'link.php?act=add');
	    include tpl('list_link');
	break;

	case 'add':
		$maxorder = $db->getOne("SELECT MAX(linkorder) FROM {$table}link");
		$maxorder = $maxorder + 1;
		$here = "添加链接";
		$action = array('name'=>'链接列表', 'href'=>'link.php?act=list');
	    include tpl('add_link');
	break;

	case 'insert':
		if(empty($_POST['webname']))show("请填写链接名称");
		if(empty($_POST['url']))show("请填写链接地址");

		$webname   = trim($_POST['webname']);
		$url       = trim($_POST['url']);
		$linkorder = intval($_POST['order']);
		$logo      = trim($_POST['logo']);
		
		if(empty($linkorder)) {
			$sql = "SELECT MAX(catorder) FROM {$table}category";
			$maxorder  = $db->getOne($sql);
			$linkorder = $maxorder + 1;
		}

		$sql = "INSERT INTO {$table}link (webname,url,linkorder,logo) VALUES ('$webname','$url','$linkorder','$logo')";
		$res = $db->query($sql);
		clear_caches('phpcache', 'link');
		admin_log("添加链接 $webname 成功");
		show('添加链接成功','link.php?act=add');
	break;

	case 'edit':
	    $linkid = intval($_REQUEST['linkid']);
		$sql = "SELECT * FROM {$table}link WHERE id = '$linkid'";
		$link = $db->getRow($sql);
		$here = "编辑链接";
		$action = array('name'=>'链接列表', 'href'=>'link.php?act=list');
		include tpl('edit_link');
	break;

	case 'update':
		if(empty($_POST['webname']))show("请填写链接名称");
		if(empty($_POST['url']))show("请填写链接地址");

        $linkid    = intval($_POST['linkid']);
		$webname   = trim($_POST['webname']);
		$url       = trim($_POST['url']);
		$linkorder = intval($_POST['order']);
		$logo      = trim($_POST['logo']);
		
		if(empty($linkorder)) {
			$sql = "SELECT MAX(catorder) FROM {$table}category";
			$maxorder  = $db->getOne($sql);
			$linkorder = $maxorder + 1;
		}
		$sql = "UPDATE {$table}link SET webname='$webname',url='$url',linkorder='$linkorder',logo='$logo' WHERE id = '$linkid'";
		$res = $db->query($sql);
		clear_caches('phpcache', 'link');
		admin_log("编辑链接 $webname 成功");
		$link = "link.php?act=list";
		show('编辑链接成功', $link);
	break;

	case 'delete':
		$id = intval($_REQUEST['linkid']);
		if(empty($id))show('没有选择记录');
		$sql = "DELETE FROM {$table}link WHERE id='$id'";
	    $res = $db->query($sql);
		clear_caches('phpcache', 'link');
		admin_log("删除链接 $id 成功");
		show('删除链接成功', 'link.php?act=list');
	break;

	case 'batch':
		$id = !empty($_POST['id']) ? join(',', $_POST['id']) : 0;
		if(empty($id))show('没有选择记录');
		$sql = "DELETE FROM {$table}link WHERE id IN ($id)";
        $re = $db->query($sql);

		clear_caches('phpcache', 'link');
		admin_log("删除链接 $id 成功");
		show('删除链接成功', 'link.php?act=list');
	break;
}
?>