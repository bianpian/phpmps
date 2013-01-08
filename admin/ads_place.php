<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('ads_place');

//初始化act操作
$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$sql = "SELECT * FROM {$table}ads_place";
		$place = $db->getAll($sql);
		$here = "广告位列表";
		$action = array('name'=>'添加广告位', 'href'=>'ads_place.php?act=add');
	    include tpl('list_ads_place');
	break;

	case 'add':
		$action = array('name'=>'广告位列表', 'href'=>'ads_place.php?act=list');
		include tpl('add_ads_place');
	break;

	case 'insert':
		if(empty($_POST['placename']))show("请填广告位名称");
		if(empty($_POST['width']))show("请填写宽度");
		if(empty($_POST['height']))show("请填写高度");
		
		$placename = trim($_POST['placename']);
		$width     = intval($_POST['width']);
		$height    = intval($_POST['height']);
		$introduce = trim($_POST['introduce']);

		$sql = "INSERT INTO {$table}ads_place (placename,width,height,introduce) VALUES ('$placename','$width','$height','$introduce')";
		$res = $db->query($sql);

		admin_log("添加广告位 $title 成功");
		$link = 'ads_place.php?act=add';
		show('添加广告位成功', $link);
	break;
	
	case 'edit':
		$id = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}ads_place WHERE placeid = '$id'"; 
		$place = $db->getRow($sql);
		include tpl('edit_ads_place');
	break;

	case 'update':
		if(empty($_POST['placename']))show("请填广告位名称");
		if(empty($_POST['width']))show("请填写宽度");
		if(empty($_POST['height']))show("请填写高度");
		
		$id        = intval($_POST['id']);
		$placename = trim($_POST['placename']);
		$width     = trim($_POST['width']);
		$height    = intval($_POST['height']);
		$introduce = trim($_POST['introduce']);

		$sql = "UPDATE {$table}ads_place SET 
		placename='$placename',
		width='$width',
		height='$height',
		introduce='$introduce' 
		WHERE placeid = '$id' ";
		$res = $db->query($sql);

		admin_log("修改广告位 $placename 成功");
		$link = 'ads_place.php?act=list';
		show('修改广告位成功', $link);
	break;

	case 'delete':
		$id = intval($_REQUEST['id']);
		if(empty($id))show('没有选择记录');

		//验证是否有广告
		$sql = "select count(*) from {$table}ads where placeid='$id'";
		$count = $db->getOne($sql);
		if($count>0)show('此广告位下有广告，不能删除');

		$sql = "DELETE FROM {$table}ads_place WHERE placeid='$id'";
	    $res = $db->query($sql);
		admin_log("删除广告位 $id 成功");
		$link = 'ads_place.php?act=list';
		show('删除广告位成功', $link);
	break;
}
?>