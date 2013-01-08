<?php
//在线支付配置已经记录文件
define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';
include_once dirname(__FILE__) . '/include/pay.fun.php';

$STATUS = array(0=>'<font color="blue">未知记录</font>', 1=>'<font color="red">支付成功</font>', 2=>'支付失败');
$today = date('Y-m-d');
extract($_REQUEST);

if($_REQUEST['act'] == 'setting')
{
	$settings = array();
	$result = $db->query("SELECT * FROM {$table}payment ORDER BY id");
	while($r = $db->fetchrow($result))
	{
		$receiveurl = PHPMPS_PATH.'respond.php';
		if($r['receiveurl'] == '') $r['receiveurl'] = $receiveurl;
		$settings[] = $r;
	}
	include tpl('payonline_setting');
}
elseif($_REQUEST['act'] == 'act_setting')
{
	$name = $_POST['name'];
	foreach($name as $id=>$v) {
		$db->query("UPDATE {$table}payment SET enable='$enable[$id]',name='$name[$id]',logo='$logo[$id]',sendurl='$sendurl[$id]',receiveurl='$receiveurl[$id]',partnerid='$partnerid[$id]',keycode='$keycode[$id]',percent='$percent[$id]',email='$email[$id]' where id='$id'");
	}
	clear_caches('phpcache', 'pay_setting');
	show('设置成功', 'payonline.php?act=setting');
}
elseif($_REQUEST['act'] == 'check')
{
	$payid = intval($payid);
	$r = $db->getRow("SELECT * FROM {$table}pay_online WHERE payid=$payid");
	if(!$r) show($LANG['order_not_exist']);
	if($r['status'] == 1) show('支付已经成功，不需要审核');
	$amount = $r['amount'];
	$username = $r['username'];
	$db->query("UPDATE {$table}pay_online SET status=1, receivetime='".time()."' WHERE payid=$payid");
	money_add($username, $amount, 'onlinepay check');

	$r = $db->getOne("SELECT money FROM {$table}member WHERE username='$username'");
	$money = $r['money'];
	$year = date('Y', time());
	$month = date('m', time());
	$date = date('Y-m-d', time());
	$time = time();
	$ip = get_ip();
	$db->query("INSERT INTO {$table}pay (typeid,note,paytype,amount,balance,username,year,month,date,inputtime,inputer,ip) VALUES('1','支付充值','在线支付','$amount','$money','$username','$year','$month','$date','$time','$_username','$ip')");
	show('审核成功', $forward);
}
elseif($_REQUEST['act'] == 'delete')
{
	$payid = is_array($id) ? implode(',', $id) : intval($id);
	$db->query("DELETE FROM {$table}pay_online WHERE payid IN ($payid)");
	show('操作成功', $_SERVER['HTTP_REFERER']);
}
elseif($_REQUEST['act'] == 'view')
{
	$payid = intval($payid);
	$r = $db->getRow("SELECT * FROM {$table}pay_online WHERE payid=$payid");
	if(!$r) show('记录不存在');
    extract($r);
	$sendtime = date('Y-m-d h:i:s', $sendtime);
	$receivetime = $receivetime ? date('Y-m-d h:i:s', $receivetime) : '';
	include tpl('payonline_view');
}
else
{
	$page = isset($page) ? intval($page) : 1;
	$pagesize = 30;
	$sql = isset($status) ? ($status ? " WHERE status=$status " : " WHERE status=0 ") : '';
	if(isset($date)) {
		$todaytime = strtotime($date.' 00:00:00');
		$tomorrowtime = strtotime($date.' 23:59:59');
	    $sql .= $sql ? " and sendtime>=$todaytime and sendtime<=$tomorrowtime" : " where sendtime>=$todaytime and sendtime<=$tomorrowtime";
	}

	$r = $db->getOne("SELECT count(*) FROM {$table}pay_online $sql");
	$pager['search'] = array();
	$pager = get_pager('payonline.php',$pager['search'], $r, $page, $pagesize);

	$pays = array();
	$result = $db->query("SELECT * FROM {$table}pay_online $sql ORDER BY payid DESC LIMIT $pager[start],$pager[size]");
	while($r = $db->fetchrow($result)) {
		$pays[] = $r;
	}
	include tpl('payonline');
}
?>