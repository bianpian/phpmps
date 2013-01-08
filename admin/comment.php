<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('comment');

//初始化act操作
$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$page = empty($_REQUEST[page])? 1 : intval($_REQUEST['page']);
		$sql = "SELECT COUNT(*) FROM {$table}comment";
		$count = $db->getOne($sql);
		$pager = get_pager('comment.php',array('act'=>'list'),$count,$page,'20');
		$sql = "SELECT * FROM {$table}comment ORDER BY id DESC LIMIT $pager[start],$pager[size]";
		$res = $db->query($sql);
		$comment = array();
		while($row=$db->fetchRow($res)) {
			$row['username'] = $row['username'] ? $row['username'] : '游客发布';
			$row['postdate'] = date('Y-m-d', $row['postdate']);
			$row['is_check'] = $row['is_check'] == '1' ? '是' : '否' ;
			$row['content']  = cut_str($row['content'], 15);
			$comment[]       = $row;
		}
		$here = "评论列表";
		$action = array('name'=>'', 'href'=>'');
	    include tpl('list_comment');
	break;
	
	case 'view':
		$id = intval($_REQUEST['id']);
		$comment = $db->getRow("SELECT * FROM {$table}comment WHERE id = '$id'");
		@extract($comment);
		$username  = $row['username'] ? $row['username'] : '游客发布';
		$postdate  = date('Y年m月d日', $row['postdate']);

		$refer  = $_SERVER['HTTP_REFERER'];
		include tpl('view_comment');
	break;

	case 'batch':
		$id = is_array($_REQUEST['id']) ? join(',', $_REQUEST['id']) : intval($_REQUEST['id']);
		if(empty($id))show('没有选择记录');
		switch ($_REQUEST['type'])
		{
			case 'delete':
				$sql = "DELETE FROM {$table}comment WHERE id IN ($id)";
				$re = $db->query($sql);
				admin_log("删除评论 $id 成功");
				show('删除成功', 'comment.php?act=list');
			break;

			case 'is_check':
				$sql = "UPDATE {$table}comment SET is_check=1 WHERE id IN ($id)";
				$re = $db->query($sql);
				admin_log("审核评论 $id 成功");
				show('审核成功', $_SERVER['HTTP_REFERER']);
			break;

			case 'no_check':
				$sql = "UPDATE {$table}comment SET is_check=0 WHERE id IN ($id)";
				$re = $db->query($sql);
				admin_log("取消审核评论 $id 成功");
				show('取消审核成功', $_SERVER['HTTP_REFERER']);
			break;
		}
	break;
}
?>