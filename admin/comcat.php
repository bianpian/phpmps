<?php

define('IN_PHPMPS', true);
require_once 'include/common.php';
require_once PHPMPS_ROOT . "include/fckeditor/fckeditor.php";
require '../include/com.fun.php';

chkadmin('category');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$cat = get_com_cat_list();
		$here = "分类列表";
		$action = array('name'=>'添加分类', 'href'=>'comcat.php?act=add');
	    include tpl('list_com_cat', 'com');
	break;

	case 'add':
        $cat = get_com_cat_list();
		$cats = $db->getAll("SELECT * from {$table}com_cat WHERE parentid=0");
		$maxorder = $db->getOne("SELECT MAX(catorder) FROM {$table}com_cat");
		$maxorder = $maxorder + 1;

		$here = "添加分类";
		$action = array('name'=>'分类列表', 'href'=>'comcat.php?act=list');
	    include tpl('add_com_cat', 'com');
	break;

	case 'insert':
		if(empty($_REQUEST[catname]))show("请填写分类名称");
		$len = strlen($_REQUEST[catname]);
		if($len < 2 || $len > 30)show("分类名必须在2个至30个字符之间");

		$catname     = trim($_REQUEST['catname']);
	    $parentid    = intval($_REQUEST['parentid']);
		$catorder    = intval($_REQUEST['catorder']);
		$keywords    = trim($_REQUEST['keywords']);
		$description = trim($_REQUEST['desc']);

		if(empty($catorder)) {
			$sql = "SELECT MAX(catorder) FROM {$table}com_cat";
			$maxorder = $db->getOne($sql);
			$catorder = $maxorder + 1;
		}
		$sql = "INSERT INTO {$table}com_cat (catname,keywords,description,parentid,catorder) VALUES ('$catname','$keywords','$description','$parentid','$catorder')";
		$res = $db->query($sql);

		clear_caches('phpcache');
		admin_log("插入分类 $cataname 成功");
		show('添加分类成功','comcat.php?act=add');
	break;

	case 'edit':
	    $catid = intval($_REQUEST['catid']);
		$sql = "SELECT * FROM {$table}com_cat WHERE catid = '$catid'";
		$cat = $db->getRow($sql);
		$sql  = "SELECT * FROM {$table}com_cat WHERE parentid = '0'";
	    $cats = $db->getAll($sql);

		include tpl('edit_com_cat', 'com');
	break;
	
	case 'update':
		if(empty($_REQUEST[catname]))show("请填写分类名称");
		$len = strlen($_REQUEST[catname]);
		if($len < 2 || $len > 30)show("分类名必须在2个至30个字符之间");
        
		$catid       = intval($_REQUEST['catid']);
		$catname     = trim($_REQUEST['catname']);
	    $parentid    = intval($_REQUEST['parentid']);
		$catorder    = intval($_REQUEST['catorder']);
		$keywords    = trim($_REQUEST['keywords']);
		$description = trim($_REQUEST['desc']);

		$sql = "UPDATE {$table}com_cat SET catname='$catname',keywords='$keywords',description='$description',parentid='$parentid',catorder='$catorder' WHERE catid = '$catid'";
		$res = $db->query($sql);
		
		clear_caches('phpcache');
		admin_log("编辑分类 $catname 成功");
		$link = "comcat.php?act=list";
		show('编辑分类成功', $link);
	break;

	case 'delete':
		$catid = intval($_REQUEST['catid']);
		if(empty($catid))show('没有选择记录');
		
		$sql = "SELECT COUNT(*) FROM {$table}com_cat WHERE parentid = '$catid' ";
		if($db->getOne($sql)>0)show('该分类下有分类，无法删除');
		
		$sql = "SELECT COUNT(*) FROM {$table}com WHERE catid = '$catid' ";
		if($db->getOne($sql)>0)show('该分类下有信息，无法删除');

		$sql = "DELETE FROM {$table}com_cat WHERE catid='$catid'";
	    $db->query($sql);

		clear_caches('phpcache');
		admin_log("删除分类 $catid 成功");
		$link = 'comcat.php?act=list';
		show('删除分类成功', $link);
	break;
}
?>