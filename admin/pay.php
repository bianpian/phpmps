<?php
define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';
include_once dirname(__FILE__) . '/include/pay.fun.php';

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch($_REQUEST['act'])
{
    case 'add':
		if($_POST['dosubmit'])
		{
			extract($_REQUEST);
            $amount = floatval($amount);
			$r = $db->getOne("SELECT userid,email,money FROM {$table}member WHERE username='$username'");
			if(!$r) show('不存在此用户');

			$balance = $r['money'];
			$value = 0;
            if($operation == '+')
			{
			    $balance += $amount;
			}
			elseif($operation == '-')
			{
			    $balance -= $amount;
			}
			$balance = round($balance, 2);
			$time = time();
			$year = date('Y', $time);
			$month = date('m', $time);
			$date = date('Y-m-d', $time);

			$note = htmlspecialchars_deep($note);
			$note = cut_str($note, 200);

            if($operation == '+')
			{
			    money_add($username, $amount, $note);
			}
			elseif($operation == '-')
			{
			    money_diff($username, $amount, $note);
			}
			$url = 'pay.php?act=add';
			show('操作成功', $url);
		}
		else
		{
			if(!isset($typeid)) $typeid = 0;
			if(!isset($username)) $username = '';
			if(!isset($amount)) $amount = '';
			if(!isset($note)) $note = '';
			include tpl('add_pay');
		}
	break;

	case 'list':
		extract($_POST);
		$typeid = isset($typeid) ? intval($typeid) : 0;
		$pagesize = '20';
		if(!isset($page))$page=1;

		$sql = '';
		$sql .= $typeid ? " and typeid=$typeid" : "";
		$sql .= isset($paytype) && $paytype ? " and paytype='$paytype'" : "";
		$sql .= isset($date) && $date ? " and date='$date'" : "";
		$sql .= isset($fromdate) && $fromdate ? " and date>='$fromdate'" : "";
		$sql .= isset($todate) && $todate ? " and date<='$todate'" : "";
		$sql .= isset($keywords) && $keywords ? " and note like '%$keywords%'" : "";
		$sql .= isset($username) && $username ? " and username='$username'" : "";
		$r = $db->getOne("select COUNT(*) as number from {$table}pay where deleted=0 $sql");
		$pager['search'] = array('act'=>'list');
		$pager = get_pager('pay.php',$pager['search'],$r, $page, $pagesize);

        $pays = $money = array();
		$result = $db->query("select * from {$table}pay where deleted=0 $sql order by payid desc limit $pager[start],$pager[size]");
		while($r = $db->fetchrow($result)) {
			$money[$r['typeid']][] = $r['amount'];
			$pays[] = $r;
		}
		$fromdate = isset($fromdate) ? $fromdate : date('Y-m-01');
		$todate = isset($todate) ? $todate : date('Y-m-d');

		include tpl('list_pay');
	break;

	case 'delete':
		$payid = intval($_REQUEST['id']);
		if(!$payid)show('没有选择记录');
		$db->query("DELETE FROM {$table}pay WHERE payid=$payid ");
		show('操作成功', "pay.php?act=list");
	break;
}
?>