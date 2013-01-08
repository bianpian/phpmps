<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('custom');

//初始化act操作
$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$cat_list = cat_options();
		$catid = isset($_REQUEST['catid']) ? intval($_REQUEST['catid']) : 0;
		$sql = "SELECT * FROM {$table}custom where catid='$catid'";
		$res = $db->query($sql);
		$cus = array();
		$type = array('单行输入','列表','复选');
		while($row = $db->fetchRow($res)) {
			$row['custype']   = $type[$row['custype']];
			$row['is_search'] = $row['search']>0 ? '是' : '否';
			$cus[] = $row;
		}
		include tpl('list_custom');
	break;

	case 'add':
		$cat_list = cat_options();
		include tpl('add_custom');
	break;

	case 'insert':
		$cusname = trim($_POST['cusname']);
		$catid   = intval($_POST['catid']);
		$search  = intval($_POST['search']);
		$custype = intval($_POST['custype']);
		$value   = trim($_POST['value']);
		$unit    = trim($_POST['unit']);
		
		if(empty($cusname))show("请填属性名称");
		if(empty($catid))show("请填写分类");
		if(($custype=='1' || $custype=='2') && empty($_POST['value']))show("请填写列表属性值");
		
		$sql = "INSERT INTO {$table}custom (catid,cusname,custype,value,search,unit,listorder) VALUES ('$catid','$cusname','$custype','$value','$search','$unit','$listorder')";
		$db->query($sql);
		
		clear_caches('phpcache');
		admin_log("添加自定义属性 $cusname 成功");
		$link = 'custom.php?act=add';
		show('添加自定义属性成功', $link);
	break;

	case 'edit':
		$id = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}custom WHERE cusid = '$id' ";
		$custom = $db->getRow($sql);
		@extract($custom, EXTR_PREFIX_SAME, '');
		$cat_list = cat_options($catid);
		foreach((array)$cats AS $cat) {
			$option .= "<option value=$cat[catid] style='color:red;'";
			$option .= ($catid == $cat[catid]) ? " selected='selected'" : '';
			$option .= ">$cat[catname]</option>";
		}
		include tpl('edit_custom');
	break;

	case 'update':
		if(empty($_REQUEST['cusname']))show("请填属性名称");
		if(empty($_REQUEST['catid']))show("请填写分类");
		if(($custype=='1' || $custype=='2') && empty($_POST['value']))show("请填写列表属性值");
		
		$id        = intval($_REQUEST['id']);
		$cusname   = trim($_REQUEST['cusname']);
		$catid     = intval($_REQUEST['catid']);
		$is_search = intval($_REQUEST['is_search']);
		$custype   = intval($_REQUEST['custype']);
		$value	   = trim($_REQUEST['value']);
		$unit      = trim($_REQUEST['unit']);

		$sql = "UPDATE {$table}custom SET catid='$catid',cusname='$cusname',custype='$custype',value='$value',search='$is_search',listorder='$listorder',unit='$unit' WHERE cusid = '$id' ";
		$db->query($sql);
		
		clear_caches('phpcache');
		admin_log("编辑自定义属性 $cusname 成功");
		$link = trim($_REQUEST['refer']);
		show('编辑自定义属性成功', $link);
	break;

	case 'delete':
		$id = intval($_REQUEST['id']);
		if(empty($id))show('没有选择记录');
		$row = $db->getRow("select * from {$table}custom where cusid='$id' ");

	    $db->query("delete from {$table}custom where cusid='$id'");
		$db->query("delete from {$table}cus_value where cusid='$id'");
		
		clear_caches('phpcache');
		admin_log("删除属性 $id 成功");
		$link = 'custom.php?act=list';
		show('删除属性', $link);
	break;
}
?>
