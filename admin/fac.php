<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('fac');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch($_REQUEST['act'])
{
	case 'list':
		$sql = "SELECT * FROM {$table}facilitate  ORDER BY id";
		$fac = array();
		$fac = $db->getAll($sql);
	    include tpl('list_fac');
	break;

	case 'add':
		$maxorder  = $db->getOne("SELECT MAX(listorder) FROM {$table}facilitate ");
		$listorder = $maxorder + 1;
		$here = "添加便民信息";
		$action = array('name'=>'便民信息列表', 'href'=>'bm.php?act=list');
	    include tpl('add_fac');
	break;

	case 'insert':
		if(empty($_POST['title']))show("请填写名称");
		if(empty($_POST['phone']))show("请填写电话");

		$title      = trim($_POST['title']);
		$phone      = trim($_POST['phone']);
		$introduce  = trim($_POST['introduce']);
		$listorder  = intval($_POST['listorder']);
		$updatetime = time();

		if(empty($listorder)) {
			$sql = "SELECT MAX(listorder) FROM {$table}facilitate";
			$maxorder  = $db->getOne($sql);
			$listorder = $maxorder + 1;
		}

		$sql = "INSERT INTO {$table}facilitate (title,phone,introduce,listorder,updatetime) VALUES ('$title','$phone','$introduce','$listorder','$updatetime')";
		$res = $db->query($sql);
		clear_caches('phpcache', 'fac');
		admin_log("添加便民信息 $title 成功");
		show('添加便民信息成功','fac.php?act=add');
	break;

	case 'edit':
	    $id = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}facilitate WHERE id = '$id'";
		$fac = $db->getRow($sql);
		$here = "编辑便民信息";
		$action = array('name'=>'便民信息列表', 'href'=>'fac.php?act=list');
		include tpl('edit_fac');
	break;

	case 'update':
		if(empty($_POST['title']))show("请填写名称");
		if(empty($_POST['phone']))show("请填写电话");
		
		$id        = intval($_POST['id']);
		$title     = trim($_POST['title']);
		$phone     = trim($_POST['phone']);
		$introduce = trim($_POST['introduce']);
		$listorder = intval($_POST['listorder']);
		$updatetime = time();

		if(empty($listorder)) {
			$sql = "SELECT MAX(listorder) FROM {$table}facilitate";
			$maxorder  = $db->getOne($sql);
			$listorder = $maxorder + 1;
		}

		$sql = "UPDATE {$table}facilitate SET 
				title='$title',
				phone='$phone',
				introduce='$introduce',
				listorder='$listorder',
				updatetime='$updatetime'
				WHERE id = '$id'";
		$res = $db->query($sql);
		clear_caches('phpcache','fac');

		admin_log("编辑便民信息 $title 成功");
		$link = "fac.php?act=list";
		show('编辑便民信息成功', $link);
	break;

	case 'batch':
		$id = !empty($_POST['id']) ? join(',', $_POST['id']) : 0;
		if(empty($id))show('没有选择记录');

		$sql = "DELETE FROM {$table}facilitate WHERE id IN ($id)";
        $re = $db->query($sql);
		clear_caches('phpcache', 'fac');
		admin_log("删除便民信息 $id 成功");
		show('删除便民信息成功', 'fac.php?act=list');
	break;
}
?>