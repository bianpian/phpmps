<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';
require_once PHPMPS_ROOT . 'include/json.class.php';

chkadmin('replace');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$sql = "SHOW TABLES LIKE '".$table."%'";
        $res=$db->Query($sql);
        while($row=$db->fetcharray($res)) {
			$channels[]=$row;
        }
		include tpl('replace');
	break;

    case 'str_replace':
		$tables = trim($_POST['tables']);
		$f = trim($_POST['f']);
		$search = trim($_POST['search']);
		$replace = trim($_POST['replace']);

		if($_POST['sql'])$sql=" and ". $_POST['sql'];
		$keys_res  = $db->query("show keys from $tables");
		$arr_keys  = $db->fetchrow($keys_res);
		$key_field = $arr_keys['Column_name'];
		$sql = "select $key_field,$f from $tables where $f like '%".$search."%' $sql ";
		$res=$db->query($sql);
		while($row=$db->fetchrow($res)) {
			$val = str_replace($search,$replace,$row[$f]);
			$val = trim($val);
			$sql = "update $tables set $f='$val' where $key_field=$row[$key_field] ";
			$db->query($sql);
			$count++; 
		}
		show('成功替换'.$count.'条信息');
	break;

	case 'ajax':
		$tablename = $_REQUEST['tables'];
		$field_result=$db->query("show fields from $tablename");
		while($row=$db->fetcharray($field_result)) {
			$name[]=$row['Field'];
		}
		$json = new Services_JSON;
		$data=$json->encode($name);
		echo $data;
	break;
}
?>