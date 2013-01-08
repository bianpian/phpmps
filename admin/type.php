<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';
require_once PHPMPS_ROOT . "include/fckeditor/fckeditor.php";

chkadmin('type');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;
$module = $_REQUEST['module'];

switch ($_REQUEST['act'])
{
	case 'list':
		$sql = "select * from {$table}type where module='$module' ";
		$type = $db->getAll($sql);
		$here = "分类列表";
		$action = array('name'=>'添加分类', 'href'=>"type.php?act=add&module=$module ");
	    include tpl('list_type');
	break;

	case 'add':
		$maxorder = $db->getOne("SELECT MAX(listorder) FROM {$table}type where module='$module'");
		$listorder = $maxorder + 1;
		$here = "添加分类";
		$action = array('name'=>'分类列表', 'href'=>'type.php?act=list&module=$module');
	    include tpl('add_type');
	break;

	case 'insert':
		if(empty($_REQUEST['typename']))show("请填写分类名称");
		$len = strlen($_REQUEST['typename']);
		if($len<2 || $len>30)show("分类名必须在2个至30个字符之间");

		$typename    = trim($_REQUEST['typename']);
		$listorder   = intval($_REQUEST['listorder']);
		$keywords    = trim($_REQUEST['keywords']);
		$description = trim($_REQUEST['description']);

		if(empty($listorder)) {
			$sql = "SELECT MAX(listorder) FROM {$table}type";
			$maxorder = $db->getOne($sql);
			$listorder = $maxorder + 1;
		}
		$sql = "INSERT INTO {$table}type (typename,listorder,keywords,description,module) VALUES ('$typename','$listorder','$keywords','$description','$module')";
		$res = $db->query($sql);
		
		admin_log("插入分类 $typeaname 成功");
		show('添加分类成功',"type.php?act=add&module=$module");
	break;

	case 'edit':
	    $typeid = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}type WHERE typeid = '$typeid'";
		$type = $db->getRow($sql);
		
		$here = "编辑分类";
		$action = array('name'=>'分类列表', 'href'=>"type.php?act=list&module=$type[module]");
		include tpl('edit_type');
	break;

	case 'update':
		if(empty($_REQUEST['typename']))show("请填写分类名称");
		$len = strlen($_REQUEST['typename']);
		if($len<2 || $len>30)show("分类名必须在2个至30个字符之间");
		
		$typeid      = intval($_REQUEST['typeid']);
		$typename    = trim($_REQUEST['typename']);
		$listorder   = intval($_REQUEST['listorder']);
		$keywords    = trim($_REQUEST['keywords']);
		$description = trim($_REQUEST['description']);

		if(empty($listorder)) {
			$sql = "SELECT MAX(listorder) FROM {$table}type where module='$module'";
			$maxorder = $db->getOne($sql);
			$listorder = $maxorder + 1;
		}
		$sql = "UPDATE {$table}type SET typename='$typename',listorder='$listorder',keywords='$keywords',description='$description' WHERE typeid = '$typeid'";
		$res = $db->query($sql);

		admin_log("编辑分类 $typename 成功");
		$link = "type.php?act=list&module=$module";
		show('编辑分类成功', $link);
	break;

	case 'delete':
		$typeid = intval($_REQUEST['id']);
		if(empty($typeid))show('没有选择记录');

		$sql = "SELECT COUNT(*) FROM {$table}help WHERE typeid = '$typeid' ";
		if($db->getOne($sql)>0)show('该分类下有信息，无法删除');

	    $db->query("DELETE FROM {$table}type WHERE typeid='$typeid'");

		admin_log("删除分类 $typeid 成功");
		$link = "type.php?act=list";
		show('删除分类成功', $link);
	break;
}
?>