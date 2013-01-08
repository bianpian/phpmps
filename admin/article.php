<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';
require PHPMPS_ROOT . "include/fckeditor/fckeditor.php";

chkadmin('article');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch($_REQUEST['act'])
{
	case 'list':
		$page = empty($_REQUEST[page])? 1 : intval($_REQUEST['page']);
		$sql = "SELECT COUNT(*) FROM {$table}article order by id desc";
		$count = $db->getOne($sql);
		$pager = get_pager('article.php',array('act'=>'list'),$count,$page,'20');

		$sql = "SELECT a.*,t.typename FROM {$table}article as a left join {$table}type as t on t.typeid=a.typeid ORDER BY id DESC LIMIT $pager[start],$pager[size]";
		$res = $db->query($sql);
		$data = array();
		while($row=$db->fetchRow($res)) {
			$row['addtime']  = date('Y年m月d日', $row['addtime']);
			$row['is_index'] = $row['is_index']=='1'?'是':'否';
			$row['is_pro']   = $row['is_pro']=='1'?'是':'否';
			$data[] = $row;
		}
		$here = "新闻列表";
		$action = array('name'=>'添加新闻', 'href'=>'article.php?act=add');
	    include tpl('list_article','article');
	break;

	case 'add':
		$maxorder = $db->getOne("SELECT MAX(listorder) FROM {$table}article");
		$maxorder = $maxorder + 1;
		
		$type_select = type_select('article');
		$content = fck_editor('content','Normal');
		$here = "添加单页";
		$action = array('name'=>'单页列表', 'href'=>'article.php?act=list');
		include tpl('add_article','article');
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
		$is_pro    = intval($_POST['is_pro']);
		$addtime     = time();

		if(empty($listorder)) {
			$sql = "SELECT MAX(listorder) FROM {$table}article";
			$maxorder  = $db->getOne($sql);
			$listorder = $maxorder + 1;
		}

		$sql = "INSERT INTO {$table}article (title,typeid,keywords,description,content,listorder,addtime,is_index,is_pro) VALUES ('$title','$typeid','$keywords','$description','$content','$listorder','$addtime','$is_index','$is_pro')";
		$res = $db->query($sql);

		admin_log("添加新闻 $title 成功");
		$link = 'article.php?act=list';
		show('添加新闻成功', $link);
	break;
	
	case 'edit':
		$id = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}article WHERE id = '$id'";
		$article = $db->getRow($sql);
		$type_select = type_select('article',$article['typeid']);

		$content = fck_editor('content','Normal',$article['content']);
		include tpl('edit_article','article');
	break;

	case 'update':
		if(empty($_POST['title']))show("请填标题");
		if(empty($_POST['content']))show("请填写详细内容");
		
		$id          = intval($_POST['id']);
		$typeid      = intval($_POST['typeid']);
		$title       = htmlspecialchars(trim($_POST['title']));
		$content     = trim($_POST['content']);
		$keywords    = trim($_POST['keyword']);
		$description = trim($_POST['description']);
		$listorder   = intval($_POST['listorder']);
		$is_index    = intval($_POST['is_index']);
		$is_pro    = intval($_POST['is_pro']);

		if(empty($listorder)) {
			$sql = "SELECT MAX(listorder) FROM {$table}article";
			$maxorder  = $db->getOne($sql);
			$listorder = $maxorder + 1;
		}

		$sql = "UPDATE {$table}article SET title='$title',typeid='$typeid',keywords='$keywords',description='$description',content='$content',listorder='$listorder',is_index='$is_index',is_pro='$is_pro' WHERE id='$id' ";
		$res = $db->query($sql);

		admin_log("修改新闻 $title 成功");
		$link = 'article.php?act=list';
		show('修改新闻成功', $link);
	break;

	case 'batch':
		$id = !empty($_POST['id']) ? join(',', $_POST['id']) : 0;
		if(empty($id))show('没有选择记录');
		$sql = "DELETE FROM {$table}article WHERE id IN ($id)";
        $re = $db->query($sql);
		admin_log("删除新闻 $id 成功");
		$link = 'article.php?act=list';
		show('删除新闻成功', $link);
	break;
}
?>