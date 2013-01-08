<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('report');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$sql = "SELECT * FROM {$table}ver ORDER BY vid DESC ";
		$data = $db->getAll($sql);

		$here = "问题验证列表";
		$action = array('name'=>'', 'href'=>'');
	    include tpl('list_ver');
	break;
	
	case 'do':
		$delete = $_REQUEST['id'];
		$question = $_POST['question'];
		$answer = $_POST['answer'];
		$newquestion = $_POST['newquestion'];
		$newanswer = $_POST['newanswer'];

		if(is_array($delete)) {
			$db->query("DELETE FROM	{$table}ver WHERE vid IN (".implode($delete).")");
		}
		if(is_array($question)) {
			foreach($question as $key => $q) {
				$q = trim($q);
				$a = cut_str(htmlspecialchars_deep(trim($answer[$key])), 50);
				if($q && $a) {
					$db->query("UPDATE {$table}ver SET question='$q', answer='$a' WHERE vid='$key'");
				}
			}
		}
		if(is_array($newquestion) && is_array($newanswer)) {
			foreach($newquestion as $key => $q) {
				$q = trim($q);
				$a = cut_str(htmlspecialchars_deep(trim($newanswer[$key])), 50);
				if($q && $a) {
					$db->query("INSERT INTO	{$table}ver (question, answer) VALUES ('$q', '$a')");
				}
			}
		}
		show('更新成功', 'ver.php');
	break;
}
?>