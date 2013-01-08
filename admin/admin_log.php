<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('admin_log');

//初始化act操作
$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$page = empty($_REQUEST[page])? 1 : intval($_REQUEST['page']);
		$sql = "SELECT COUNT(*) FROM {$table}admin_log";
		$count = $db->getOne($sql);
		$pager = get_pager('admin_log.php',array('act'=>'list'),$count,$page,'20');
		
		$sql = "SELECT * FROM {$table}admin_log ORDER BY logid DESC LIMIT $pager[start],$pager[size]";
		$res = $db->query($sql);
		$log = array();
		while($row=$db->fetchRow($res)) {
			$row['logdate']   = date('Y-m-d', $row['logdate']);
			$log[]            = $row;
		}
	    include tpl('list_admin_log');
	break;

	case 'batch':
		$id = is_array($_REQUEST['id']) ? join(',', $_REQUEST['id']) : intval($_REQUEST['id']);
		if(empty($id))show('没有选择记录');
		$sql = "DELETE FROM {$table}admin_log WHERE logid IN ($id)";
        $re = $db->query($sql);
		show('删除成功', 'admin_log.php?act=list');
	break;
}
?>