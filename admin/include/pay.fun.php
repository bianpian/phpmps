<?php

if(!defined('IN_PHPMPS'))
{
	die('Access Denied');
}

function gold_add($username, $number, $note = '')
{
	global $db,$table;
	$number = intval($number);
	if($number < 0)	show('数量不能小于0');
	$note = addslashes($note);
	$db->query("UPDATE {$table}member SET gold=gold+$number WHERE username='$username'");
	if($db->affected_rows() == 0) show('操作失败');
	$time = time();
	$ip = get_ip();
	$db->query("INSERT INTO {$table}pay_exchange  (`username`,`type`,`value`,`note`,`addtime`,`ip`) VALUES('$username','gold','$number','$note','$time','$ip')");
}

function gold_diff($username, $number, $note = '', $authid = '')
{
	global $db, $table;
	$number = intval($number);
	if($number < 0)	show('数量不能小于0');
	$note = addslashes($note);
	$r = member_info($username,'2');
	if(!$r) show('不存在此用户');
	extract($r);
	$time = time();
	$ip = get_ip();
	if($number > $gold) show('您的金额不够支付');
	$db->query("UPDATE {$table}member SET gold=gold-$number WHERE username='$username'");
	$db->query("INSERT INTO {$table}pay_exchange  (`username`,`type`,`value`,`note`,`addtime`,`ip`) VALUES('$username','gold','-".$number."','$note','$time','$ip')");
	
	if($member['ispointdiffemail']) {
		//$data = tpl_data('member','pointmailtpl');
		sendmail($email, '确认会员金币变动邮件'.'('.$CFG['sitename'].')', stripslashes($data));
	}
	return TRUE;
}

function credit_add($username, $number, $note = '')
{
	global $db, $table;
	$number = intval($number);
	if($number < 0)	show('数量不能小于0');
	$db->query("UPDATE {$table}member SET credit=credit+$number WHERE username='$username'");
	$note = addslashes($note);
	$time = time();
	$ip = get_ip();
	if($db->affected_rows() == 0) show('添加失败');
	$db->query("INSERT INTO {$table}pay_exchange  (`username`,`type`,`value`,`note`,`addtime`,`ip`) VALUES('$username','credit','$number','$note','$time','$ip')");
}

function credit_diff($username, $number, $note = '')
{
	global $db, $table;
	$number = intval($number);
	if($number < 0)	show($LANG['illegal_parameters']);
	$note = addslashes($note);
	$r = member_info($username,'2');
	if(!$r) show('不存在此用户');
	extract($r);
	$time = time();
	$ip = get_ip();
	if($chargetype == 0)
	{
		if($number > $credit) show('您的积分不足以支付');
        $db->query("UPDATE {$table}member SET credit=credit-$number WHERE username='$username'");
	    $db->query("INSERT INTO {$table}pay_exchange  (`username`,`type`,`value`,`note`,`addtime`,`ip`) VALUES('$username','credit','-".$number."','$note','$time','$ip')");
	}
	return TRUE;
}

function money_add($username, $number, $note = '')
{
	global $db, $table;
	$number = round(floatval($number) ,2);
	if($number < 0) show('不能小于0元');
	$note = addslashes($note);
	$r = member_info($username,'2');
	if(!$r) show('不存在此用户');
	extract($r);
	$money = $money + $number;
	$db->query("UPDATE {$table}member SET money=$money WHERE username='$username'");
	if($db->affected_rows() == 0) show('操作失败');
	$time = time();
	$year = date('Y', $time);
	$month = date('m', $time);
	$date = date('Y-m-d', $time);
	$ip = get_ip();
	
	//资金变动记录
	$db->query("INSERT INTO {$table}pay (typeid,note,paytype,amount,balance,username,year,month,date,inputtime,inputer,ip) VALUES('1','$note','入款','$number','$money','$username','$year','$month','$date','$time','system','$ip')");

	$db->query("INSERT INTO {$table}pay_exchange (`username`,`type`,`value`,`note`,`addtime`,`ip`) VALUES('$username','money','$number','$note','$time','$ip')");
}

function money_diff($username, $number, $note = '')
{
	global $db, $table;

	$number = round(floatval($number) ,2);
	if($number == 0) return true;
	if($number < 0) show('不能小于0元');
	$note = addslashes($note);
	$r = member_info($username,'2');
	if(!$r) show('不存在此用户');
	extract($r);
	if($number > $money) show('帐户资金不够，请先入款！');
	$money = $money - $number;
	$db->query("UPDATE {$table}member SET money=$money WHERE username='$username'");
	
	$time = time();
	$year = date('Y', $time);
	$month = date('m', $time);
	$date = date('Y-m-d', $time);
	$ip = get_ip();
	
	//资金变动记录
	$db->query("INSERT INTO {$table}pay (typeid,note,paytype,amount,balance,username,year,month,date,inputtime,inputer,ip) VALUES('2','$note','扣款','$number','$money','$username','$year','$month','$date','$time','system','$ip')");

	$db->query("INSERT INTO {$table}pay_exchange (`username`,`type`,`value`,`note`,`addtime`,`ip`) VALUES('$username','money','-".$number."','$note','$time','$ip')");

	return true;
}
?>