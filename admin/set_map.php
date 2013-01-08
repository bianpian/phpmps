<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('config');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch($_REQUEST['act'])
{
	case 'list':
		$CFG = '';
		$sql = "select setname,value from {$table}config";
		$res = $db->query($sql);
		while($row=$db->fetchRow($res)) {
			$CFG[$row['setname']] = $row['value'];
		}
		include tpl('set_map');
	break;

	case 'set_config':
		$_POST['map']  = trim($_POST['map']);
		$_POST['mapapi']	= trim($_POST['mapapi']);
		$_POST['mapflag']	= trim($_POST['mapflag']);
		$_POST['map_view_level']	= intval($_POST['map_view_level']);
		$_POST['mapapi_charset']	= trim($_POST['mapapi_charset']);
		unset($_POST['act']);
		unset($_POST['submit']);
		
		foreach($_POST as $key=>$val) {
			$data = $db->getone("SELECT * FROM {$table}config WHERE setname='$key'");
			if($data) {
				$sql = "UPDATE {$table}config SET value = '$val' WHERE setname = '$key' ";
			} else {
				$sql = "INSERT INTO {$table}config (setname,value) VALUES ('$key','$val') ";
			}
			$res = $db->query($sql);
			$res ? $msg.='' : $msg.='1';
		}
		empty($msg) ? $msg = "修改配制成功": $msg = "修改配制失败";
		clear_caches('phpcache');
		$link = "set_map.php";
		show($msg, $link);
	break;
}

?>