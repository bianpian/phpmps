<?php
define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';

$infoid = empty($_REQUEST['infoid']) ? 0 : intval($_REQUEST['infoid']);
$page = empty($_REQUEST['page'])? 1: intval($_REQUEST['page']);
$count = $db->getOne("SELECT COUNT(*) FROM {$table}comment WHERE is_check=1 and infoid = '$infoid'");
$pager['search'] = array('infoid' => $infoid);
$pager = get_pager('comment.php', $pager['search'], $count, $page, '10');
$sql = "SELECT * FROM {$table}comment WHERE infoid = '$infoid' AND is_check = 1 ORDER by id DESC LIMIT $pager[start],$pager[size]";
$res = $db->query($sql);
$comment = array();
while($row = $db->fetchRow($res)) {
	$row['username'] = $row['username'] ? $row['username'] : '本站网友' ;
	$row['postdate'] = date('Y-m-d H:i:s', $row['postdate']);
	$comment[] = $row;
}
include template('comment');
?>