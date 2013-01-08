<?php

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';

chkadmin('area');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$area = get_area_list();
		$here = "地区列表";
		$action = array('name'=>'添加地区', 'href'=>'area.php?act=add');
	    include tpl('list_area');
	break;

	case 'add':
		$area = $db->getAll("SELECT * from {$table}area WHERE parentid=0");
		$maxorder = $db->getOne("SELECT MAX(areaorder) FROM {$table}area");
		$maxorder = $maxorder + 1;
		$here = "添加地区";
		$action = array('name'=>'地区列表', 'href'=>'area.php?act=list');
	    include tpl('add_area');
	break;

	case 'insert':
		if(empty($_REQUEST['areaname']))show("请填写地区名称");
		$len = strlen($_REQUEST['areaname']);
		if($len < 2 || $len > 30)show("地区名必须在2个至30个字符之间");
		$areaname  = trim($_REQUEST['areaname']);
		$parentid  = intval($_REQUEST['parentid']);
		$areaorder = intval($_REQUEST['areaorder']);

		if(empty($areaorder)) {
			$sql = "SELECT MAX(areaorder) FROM {$table}area";
			$maxorder = $db->getOne($sql);
			$areaorder = $maxorder + 1;
		}
		$sql = "INSERT INTO {$table}area (areaname,parentid,areaorder) VALUES ('$areaname','$parentid','$areaorder')";
		$res = $db->query($sql);
		
		clear_caches('phpcache');
		admin_log("插入地区 $areaname 成功");
		show('添加地区成功','area.php?act=add');
	break;

	case 'edit':
	    $areaid = intval($_REQUEST['areaid']);
		$sql = "SELECT * FROM {$table}area WHERE areaid = '$areaid'";
		$area = $db->getRow($sql);
		$sql  = "SELECT * FROM {$table}area WHERE parentid = '0'";
	    $areas = $db->getAll($sql);	
		$here = "编辑地区";
		$action = array('name'=>'地区列表', 'href'=>'area.php?act=list');
		include tpl('edit_area');
	break;

	case 'update':
		if(empty($_REQUEST['areaname']))show("请填写地区名称");
		$len = strlen($_REQUEST['areaname']);
		if($len < 2 || $len > 30)show("地区名必须在2个至30个字符之间");
        
		$areaid    = intval($_REQUEST['areaid']);
		$areaname  = trim($_REQUEST['areaname']);
		$parentid  = intval($_REQUEST['parentid']);
		$areaorder = intval($_REQUEST['areaorder']);

		$sql = "UPDATE {$table}area SET areaname='$areaname',
		parentid='$parentid',
		areaorder='$areaorder'
		WHERE areaid = '$areaid'";
		$res = $db->query($sql);
		clear_caches('phpcache');
		admin_log("编辑地区 $areaname 成功");        
		$link = "area.php?act=list";
		show('编辑地区成功', $link);
	break;

	case 'delete':
		$areaid = intval($_REQUEST['areaid']);
		if(empty($areaid))show('没有选择记录');
		$sql = "DELETE FROM {$table}area WHERE areaid='$areaid'";
	    $res = $db->query($sql);
		clear_caches('phpcache');
		admin_log("删除地区 $areaid 成功");
		$link = "area.php?act=list";
		show('删除地区成功', $link);
	break;
}
?>