<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';
require_once PHPMPS_ROOT . "include/fckeditor/fckeditor.php";

chkadmin('help');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch($_REQUEST['act'])
{
	case 'list':
		$page = empty($_REQUEST[page])? 1 : intval($_REQUEST['page']);
		$sql = "SELECT COUNT(*) FROM {$table}help order by id desc";
		$count = $db->getOne($sql);
		$pager = get_pager('help.php',array('act'=>'list'),$count,$page,'20');
		
		$sql = "SELECT a.*,t.typename FROM {$table}help as a left join {$table}type as t on t.typeid=a.typeid ORDER BY id DESC LIMIT $pager[start],$pager[size]";
		$res = $db->query($sql);
		$data = array();
		while($row=$db->fetchRow($res)) {
			$row['updatetime'] = date('Y-m-d', $row['updatetime']);
			$row['is_index'] = $row['is_index']=='1'?'是':'否';
			$data[] = $row;
		}
		$here = "帮助列表";
		$action = array('name'=>'添加帮助', 'href'=>'help.php?act=add');
	    include tpl('list_help');
	break;

	case 'add':
		$maxorder = $db->getOne("SELECT MAX(listorder) FROM {$table}help");
		$maxorder = $maxorder + 1;
		$type_select = type_select('help');
		$content = fck_editor('content','Normal');
		$here = "添加单页";
		$action = array('name'=>'单页列表', 'href'=>'help.php?act=list');
		include tpl('add_help');
	break;

	case 'insert':
		if(empty($_POST['title']))show("请填标题");
		if(empty($_POST['typeid']))show("请填分类");
		if(empty($_POST['content']))show("请填写详细内容");
		
		$title       = htmlspecialchars(trim($_POST['title']));
		$typeid      = intval($_POST['typeid']);
		$content     = trim($_POST['content']);
		$keywords    = trim($_POST['keyword']);
		$description = trim($_POST['description']);
		$listorder   = intval($_POST['listorder']);
		$is_index    = intval($_POST['is_index']);
		$addtime     = time();

		if(empty($listorder)) {
			$sql = "SELECT MAX(listorder) FROM {$table}help";
			$maxorder  = $db->getOne($sql);
			$listorder = $maxorder + 1;
		}
		$sql = "INSERT INTO {$table}help (title,typeid,keywords,description,content,listorder,addtime,is_index) VALUES ('$title','$typeid','$keywords','$description','$content','$listorder','$addtime','$is_index')";
		$res = $db->query($sql);

		admin_log("添加文章 $title 成功");
		$link = 'help.php?act=add';
		show('添加文章成功', $link);
	break;
	
	case 'edit':
		$id = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}help WHERE id = '$id'";
		$help = $db->getRow($sql);
		$type_select = type_select('help',$help['typeid']);
		$content = fck_editor('content','Normal',$help['content']);
		include tpl('edit_help');
	break;

	case 'update':
		if(empty($_POST['title']))show("请填标题");
		if(empty($_POST['typeid']))show("请填分类");
		if(empty($_POST['content']))show("请填写详细内容");
		
		$id          = intval($_POST['id']);
		$title       = htmlspecialchars(trim($_POST['title']));
		$typeid      = intval($_POST['typeid']);
		$content     = trim($_POST['content']);
		$keywords    = trim($_POST['keyword']);
		$description = trim($_POST['description']);
		$listorder   = intval($_POST['listorder']);
		$is_index    = intval($_POST['is_index']);

		if(empty($listorder)) {
			$sql = "SELECT MAX(listorder) FROM {$table}help";
			$maxorder  = $db->getOne($sql);
			$listorder = $maxorder + 1;
		}

		$sql = "UPDATE {$table}help SET title='$title',typeid='$typeid',keywords='$keywords',description='$description',content='$content',listorder='$listorder',is_index='$is_index' WHERE id='$id' ";
		$res = $db->query($sql);

		admin_log("修改帮助 $title 成功");
		$link = 'help.php?act=list';
		show('修改帮助成功', $link);
	break;

	case 'batch':
		$id = !empty($_POST['id']) ? join(',', $_POST['id']) : 0;
		if(empty($id))show('没有选择记录');
		$sql = "DELETE FROM {$table}help WHERE id IN ($id)";
        $re = $db->query($sql);
		admin_log("删除帮助 $id 成功");
		$link = 'help.php?act=list';
		show('删除帮助成功', $link);
	break;
}
?>