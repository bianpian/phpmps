<?php

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';
if($CFG['uc'])require PHPMPS_ROOT . 'include/uc.inc.php';
require PHPMPS_ROOT . 'include/json.class.php';
require PHPMPS_ROOT . 'include/pay.fun.php';
$act = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'index';

$not_login = array('login','act_login','register','act_register','logout',  'ajax', 'get_password', 'reset_password', 'send_pwd_email', 'email_edit_password','credit_rule','receive', 'check_info_gold', 'delinfo', 'editinfo', 'updateinfo', 'report', 'comment');

$must_login = array('index','modify','act_modify', 'edit_password', 'act_edit_password', 'info','info_comment', 'payonline', 'confirm', 'send', 'exchange', 'gold', 'act_gold', 'com_comment', 'com_list', 'editcom', 'updatecom', 'delcom','refer', 'top', 'send_info_mail' );

if(empty($_userid)) {
    if (!in_array($act, $not_login)) {
        if (in_array($act, $must_login)) {
            showmsg('请先登录', 'member.php?act=login&refer='.$PHP_URL);
        } else {
			showmsg('请不要提交非法请求！');
		}
    }
}

switch($act)
{
	case 'index':
		$seo['title'] = "会员中心".' powered by phpmps';
		$userinfo = member_info($_userid);
		extract($userinfo);
		$registertime = date('Y年m月d日', $registertime);
		if(empty($email) || empty($qq) || empty($phone))$notice=1;
		include template('member');
	break;

	case 'login':
		if(!empty($_userid)) {
			showmsg("你已经登录了", "member.php");
		}
		$verf = get_one_ver();
		$refer = trim(htmlspecialchars($_SERVER['HTTP_REFERER']));
		$seo['title'] = "用户登录";
		include template('login');
	break;

	case 'act_login':
		if(!submitcheck('submit')) showmsg('提交错误');
		$username = $_POST['username'] ? htmlspecialchars(trim($_POST['username'])) : '';
		$password = $_POST['password'] ? trim($_POST['password']) : '';
		$checkcode = $_POST['checkcode'] ? trim($_POST['checkcode']) : '';
		$md5_password = MD5($password);
		if(empty($username))showmsg("用户名不能为空");
		if(empty($password))showmsg("密码不能为空");
		//check_code($checkcode);
		check_ver(intval($_REQUEST['vid']), trim($_REQUEST['answer']));
		/* 
		 *	整合UCenter登录的思路
		 * 
		 *	一. 首先查询UCenter有没有用户名为$username的用户，分以下两种情况：
		 * 
		 *	  1.如果没有，查询本地，如果本地有，则注册到UCenter，同时登陆UCenter，登录失败则提示，如果成功，则到第二步
		 * 
		 * 	  2.如果有，判断本地有没有此用户
		 * 		(1).本地有，则更新本地用户的UCenter编号
		 * 		(2).本地没有，则插入本地
		 * 
		 * 	二. 到这步，UCenter肯定是登陆成功了，然后同步登陆UCenter
		 */
		if($CFG['uc']) {
			/* UCenter有没有用户名为$username的用户 */
			list($uid, $uc_username, $uc_password, $email) =  uc_call("uc_user_login", array($username, $password));
			
			if($uid==-2) {
				showmsg('密码错误');
			}
			/* 如果UCenter不存在此用户 */
			if($uid==-1) {
				/* 查询本地 */
				$sql = "SELECT * FROM {$table}member WHERE username='$username' and password='$md5_password'";
				$user = $db->getRow($sql);
				
				/* 如果本地有 */
				if($user['userid']>0) {
					/* 注册到UCenter */
					$uid = uc_call("uc_user_register", array($user['username'], $password, $user['email']));
			
					/* 如果注册失败，则提示 */
					if($uid<=0){showmsg('注册到ucenter失败');}
					
					/* 如果注册成功，则更新用户UCenter编号 */
					if($uid>0) {
						$db->query("UPDATE {$table}member SET uid='$uid' WHERE userid='$user[userid]' ");
					}

					/* 登录UCenter */
					list($uid, $uc_username, $uc_password, $email) =  uc_call("uc_user_login", array($username, $password));

					if($uid<0) {
						showmsg('同步登陆到UCenter失败');
					}
				}
			}

			if($uid>0) {
				/* 查询本地是否有此用户 */
				$userinfo = $db->getOne("select * from {$table}member where username='$username' and password='$md5_password' ");
				
				/* 本地和UCenter都有此用户，更新本地用户的UCenter编号 */
				if($userinfo && $userinfo['uid']!=$uid) {
					$sql = "update {$table}member set uid='$uid' where userid='$userinfo[userid]'";
					$db->query($sql);
				}

				/* 如果本地没有，插入本地 */
				if($userinfo['userid']<=0) {
					$ip = get_ip();
					$regtime = $lastlogintime = time();
					
					$sql = "insert into {$table}member (uid,username,email,password,registertime,registerip,lastlogintime) values ('$uid','$username','$email','$md5_password','$regtime','$ip','$lastlogintime')";
					$db->query($sql);
				}
			}
			$ucsynlogin = uc_call('uc_user_synlogin', array($uid));
			echo $ucsynlogin;
		}
		
		if(login($username,$md5_password)) {
			$credit_count = $db->getOne("select count(*) from {$table}pay_exchange where username='$username' and addtime>".mktime(0,0,0)." and note='login' ");
			if($credit_count < $CFG['max_login_credit']) {
				if(!empty($CFG['login_credit'])) credit_add($username, $CFG['login_credit'],'login');
			}
			$url= $_REQUEST['refer'] ? rawurldecode($_REQUEST['refer']) : 'member.php';
			showmsg("登陆成功", $url);
		} else {
			showmsg("登录失败",'member.php?act=login');
		}
	break;
	
	case 'logout':
		if($CFG['uc']) {
			$ucsynlogout = uc_call('uc_user_synlogout', array());
			echo $ucsynlogout;
		}
		logout();
		$link = "index.php";
		showmsg("退出成功",$link);
	break;

	case 'register':
		if($CFG['close_register'] == '1') showmsg('本站已关闭用户注册。');
		if(!empty($_userid)) {
			$link = "member.php?act=index";
			showmsg("请退出后再进行该操作", $link);
		}
		$ip = get_ip();
		$postarea = getPostArea($ip);
		onlyarea($postarea);

		$verf = get_one_ver();
		$mappoint = explode(',',$CFG[map]);

		$seo['title'] = "会员注册";
		include template('register');
	break;

	case 'act_register':
		$ip = get_ip();
		$postarea = getPostArea($ip);
		onlyarea($postarea);
		
		if(!submitcheck('submit')) showmsg('提交错误');

		$username   = $_POST['username'] ? htmlspecialchars(trim($_POST['username'])) : '';
		$password   = $_POST['password'] ? trim($_POST['password']) : '';
		$repassword = $_POST['repassword'] ? trim($_POST['repassword']) : '';
		$email      = $_POST['email']?trim($_POST['email']):'';
		$checkcode  = $_POST['checkcode']?trim($_POST['checkcode']):'';
		$md5_password = MD5($password);

		if(empty($username))showmsg("用户名不能为空");
		if(empty($password))showmsg("密码不能为空");
		if(empty($repassword))showmsg("重复密码不能为空");
		if(empty($email))showmsg("邮箱不能为空");
		if($password != $repassword)showmsg("两次输入的密码不相同");
		if(!is_email($email))showmsg("邮箱的格式不正确");
		//check_code($checkcode);
		check_ver(intval($_REQUEST['vid']), trim($_REQUEST['answer']));

		if($CFG['uc']){
			/* 
			查询本地是否有此用户，如果有，就应该到登陆的时候处理，登录的时候如果本地有，UCenter没有，插入UCenter。 
			*/
			$userid = $db->getOne("select userid from {$table}member where username='$username' ");
			if($userid>0){
				showmsg('已经存在此用户名');
			}

			//本地没有，则分别插入到Ucenter和Phpmps
			$uid = uc_call("uc_user_register", array($username, $password, $email));

			if($uid == -1) {
				showmsg('用户名不合法');
			} elseif($uid == -2) {
				showmsg('包含要允许注册的词语');
			} elseif($uid == -3) {
				showmsg('用户名已经存在');
			} elseif($uid == -4) {
				showmsg('Email 格式有误');
			} elseif($uid == -5) {
				showmsg('Email 不允许注册');
			} elseif($uid == -6) {
				showmsg('该 Email 已经被注册');
			}
			$regtime = $lastlogintime = time();
			$sql = "insert into {$table}member (uid,username,email,password,registertime,registerip,lastlogintime) values ('$uid','$username','$email','$md5_password','$regtime','$ip','$lastlogintime')";
			$res = $db->query($sql);
		}
		
		if(empty($res)) {
			if(register($username,$md5_password,$email)) {
				if(!empty($CFG['register_credit']))credit_add($_SESSION['username'], $CFG['register_credit'],'register');

				$link='member.php';
				showmsg("注册成功",$link);
			} else {
				$link = "member.php?act=register";
				showmsg("注册失败",$link);
			}
		}
		login($username, $md5_password);
		showmsg('注册成功', 'member.php');
	break;

	case 'modify':
		$mappoint = explode(',',$CFG['map']);
		$userinfo = member_info($_userid);
		$seo['title'] = "修改会员资料";
		include template('modify');
	break;

	case 'act_modify':
		$phone    = $_POST['phone'] ? trim($_POST['phone']) : '';
		$qq       = $_POST['qq'] ? intval($_POST['qq']) : '';
		$address  = $_POST['address'] ? trim($_POST['address']) : '';
		$mappoint = $_POST['mappoint'] ? trim($_POST['mappoint']) : '';
		$userid   = $_SESSION['userid'];
		$username = $_SESSION['username'] ? trim($_SESSION['username']) : '';
		$email    = trim($_POST['email']);

		if($CFG['uc']) {
			$result = uc_call("uc_user_edit", array($username, '', '', $email, '1'));

			if($result == -4) {
				showmsg('Email 格式有误');
			} elseif($result == -5) {
				showmsg('Email 不允许注册');
			} elseif($result == -6) {
				showmsg('该 Email 已经被注册');
			}
		}

		$sql = "update {$table}member set 
				phone = '$phone',
				qq = '$qq',
				address = '$address',
				mappoint = '$mappoint',
				email = '$email'
				where userid='$_userid' and username='$username' ";
		$res = $db->query($sql);

		showmsg('修改资料成功', 'member.php?act=modify');
	break;

	case 'edit_password':
		$seo['title'] = '修改密码';
		include template('edit_password');
	break;

	case 'act_edit_password':
		$oldpassword = $_POST['oldpassword'] ? trim($_POST['oldpassword']) : '';
		$password = $_POST['password'] ? trim($_POST['password']) : '';
		$repassword = $_POST['repassword'] ? trim($_POST['repassword']) : '';
		
		if(empty($oldpassword) && !empty($password))showmsg('请输入旧密码！');
		if($password && $repassword && $password!=$repassword)showmsg('两次输入的密码不一致！');

		$sql = "SELECT password FROM {$table}member WHERE userid='$_userid' LIMIT 1";
		if(MD5($oldpassword) != $db->getOne($sql))showmsg('旧密码输入错误');

		$password = MD5($password);
		$query = $db->query("UPDATE {$table}member SET password='$password' WHERE userid='$_userid' ");

		if($CFG['uc']) {
			$username = $_username;
			$old_password = $oldpassword;
			$new_password = $password;
			
			$result = uc_call("uc_user_edit", array($username, $old_password, $new_password, '1'));
			if($result == -1) {
				showmsg('旧密码不正确');
			}
		}
		showmsg('密码修改成功!','member.php?act=edit_password');
	break;

	case 'ajax':
		$json = new Services_JSON;
		switch($_REQUEST['type'])
		{
			case 'username':
				$username = trim($_REQUEST['username']);
				$sql = "SELECT count(*) FROM {$table}member WHERE username='$username'";
				$count = $db->getOne($sql);
				if($CFG['uc'])$uc_count = uc_call("uc_user_checkname", $username);

				if($count>0 || $uc_count<0) {
					echo $json->encode(false);
					exit;
				} else {
					echo $json->encode(true);
					exit;
				}
			break;

			case 'email':
				$count = $uc_count = 0;
				$email = trim($_REQUEST['email']);
				$sql = "SELECT userid FROM {$table}member WHERE email='$email'";
				$count = $db->getOne($sql);

				if($CFG['uc'])$uc_count = uc_call("uc_user_checkemail", $email);

				if($count>0 || $uc_count<0) {
					echo $json->encode(false);
					exit;
				} else {
					echo $json->encode(true);
					exit;
				}
			break;
		}
	break;

	case 'payonline':
		$payonline_setting = get_pay_setting();
		$paycenter = $_COOKIE['paycenter'];
		if($paycenter) $selected[$paycenter] = 'selected';
		if(!isset($amount)) $amount = '';

		$memberinfo = member_info($_userid);
		$telephone = $memberinfo['telephone'];
		$email = $memberinfo['email'];
		$seo['title'] = '在线支付-选择支付方式';
		include template('payonline');
	break;

	case 'confirm':
		$payonline_setting = get_pay_setting();
		$paycenter = trim($_POST['paycenter']);
		$contactname = trim($_POST['contactname']);
		$telephone = trim($_POST['telephone']);
		$email = trim($_POST['email']);
		
		$amount = round(floatval($_POST['amount']), 2);
		if($amount < 0.01) showmsg('不能小于0.01元');
		if(empty($contactname) || empty($telephone) || empty($email)) showmsg('请填写完整信息');

		array_key_exists($paycenter, $payonline_setting) or showmsg('不存在此支付方式');
		@extract($payonline_setting[$paycenter]);

		if($percent) {
			$percent = round(floatval($percent), 2);
			$trade_fee = round($amount*$percent/100, 2);
			if($trade_fee < 0.01) $trade_fee = 0.01;
		} else {
			$trade_fee = 0;
		}
		$total_amount = $amount + $trade_fee;
		require PHPMPS_ROOT.'include/payonline/'.$paycenter.'/confirm.php';

		$seo['title'] = '在线支付-确认订单';
		include template('payconfirm');
	break;

	case 'send':
		$paycenter = trim($_POST['paycenter']);
		$contactname = trim($_POST['contactname']);
		$telephone = trim($_POST['telephone']);
		$email = trim($_POST['email']);
		$username = trim($_POST['username']);
		$orderid = trim($_POST['orderid']);
		$time = time();
		$ip = get_ip();
		$payonline_setting = get_pay_setting();
		array_key_exists($paycenter, $payonline_setting) or showmsg('不存在此支付方式');
		@extract($payonline_setting[$paycenter]);
		setcookie('paycenter', $paycenter, time() + 3600*24*365);

		$r = $db->getOne("SELECT payid FROM {$table}pay_online WHERE `orderid`='$orderid'");
		if($r) showmsg('不要刷新');
		$moneytype = 'CNY';
		$amount = floatval($_POST['amount']);
		$trade_fee = floatval($_POST['trade_fee']);
		
		$db->query("INSERT INTO {$table}pay_online (`paycenter`,`username`,`orderid`,`moneytype`,`amount`,`trade_fee`,`contactname`,`telephone`,`email`,`sendtime`,`ip`) VALUES('$paycenter','$_username','$orderid','$moneytype','$amount','$trade_fee','$contactname','$telephone','$email','$time','$ip')");

		$amount = $amount + $trade_fee;
		require PHPMPS_ROOT.'include/payonline/'.$paycenter.'/send.php';
	break;

	case 'receive':
		extract($_REQUEST);
		$payonline_setting = get_pay_setting();
		array_key_exists($paycenter, $payonline_setting) or showmsg('支付错误');
		@extract($payonline_setting[$paycenter]);
		require PHPMPS_ROOT.'include/payonline/'.$paycenter.'/receive.php';
		
		$total_amount = $amount + $trade_fee;
		$seo['title'] = '支付返回信息';
		include template('payreceive');
	break;

	case 'exchange':
		$units = array('gold'=>'枚', 'money'=>'元', 'credit'=>'分');
		$types = array('money'=>'资金', 'gold'=>'信息币', 'credit'=>'积分');
		$notes = array('login'=>'登陆积分', 'post_info_credit'=>'发布信息积分' ,'post_comment_credit'=>'发布评论积分' ,'info_refer'=>'一键更新信息' ,'info_top'=>'信息置顶' , 'credit2gold'=>'积分兑换信息币', 'money2gold'=>'资金购买信息币');
		extract($_REQUEST);
		$page = isset($page) ? intval($page) : 1;
		$pagesize = 20;

		$sql = '';
		if($type) $sql .= " AND type='$type' ";
		if($begindate) {
			$begintime = strtotime($begindate.' 00:00:00');
			$sql .= " AND addtime>=$begintime ";
		}
		if($enddate) {
			$endtime = strtotime($enddate.' 23:59:59');
			$sql .= " AND addtime<=$endtime";
		}
		$r = $db->getOne("SELECT count(*) as number FROM {$table}pay_exchange WHERE username='$_username' $sql");
		$pager['search'] = array('act' => 'exchange');
		$pager = get_pager('member.php', $pager['search'], $r, $page, $pagesize);

		$exchanges = array();
		$result = $db->query("SELECT * FROM {$table}pay_exchange WHERE username='$_username' $sql ORDER BY exchangeid DESC LIMIT $pager[start],$pager[size]");
		while($r = $db->fetchrow($result)) {
			$r['unit'] = $units[$r['type']];
			$r['type'] = $types[$r['type']];
			$r['note'] = !empty($notes[$r['note']]) ? $notes[$r['note']] : $r['note'];
			$r['addtime'] = date('Y-m-d h:i:s', $r['addtime']);
			$exchanges[] = $r;
		}
		$seo['title'] = '交易详情';
		include template('member_exchange');
	break;

	case 'get_password':
		if(!$CFG['sendmailtype'])showmsg('尚未设置邮件服务器，请与管理员联系。');
		if (isset($_GET['code']) && isset($_GET['userid'])) {
			$code = trim($_GET['code']);
			$userid  = intval($_GET['userid']);
			/* 判断链接的合法性 */
			$user_info = member_info($userid);
			if (empty($user_info) || ($user_info && md5($user_info['userid'] . $CFG['crypt'] . $user_info['registertime']) != $code)) {
				showmsg('参数错误');
			}
			$seo['title'] = '重置密码';
			include template('reset_password');
		} else {
			$seo['title'] = '找回密码';
			include template('get_password');
		}
	break;

	case 'send_pwd_email':
		$username = !empty($_POST['username']) ? trim($_POST['username']) : '';
		$email     = !empty($_POST['email'])     ? trim($_POST['email'])     : '';
		$user_info = member_info($username,'2');

		if ($user_info && $user_info['email'] == $email) {
			$code = md5($user_info['userid'] . $CFG['crypt'] . $user_info['registertime']);
			include PHPMPS_ROOT.'include/mail.inc.php';
			if (send_pwd_email($user_info['userid'], $username, $email, $code)) {
				showmsg('发送成功' , 'index.php');
			} else {
				showmsg('发送失败' , 'index.php');
			}
		} else {
			showmsg('用户名和邮件地址不匹配');
		}
	break;

	case 'email_edit_password':
		$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
		$confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
		$userid  = isset($_POST['userid']) ? intval($_POST['userid']) : $userid;
		$code = isset($_POST['code']) ? trim($_POST['code'])  : '';

		if (strlen($new_password) < 6)showmsg('密码不能少于6位');
		if($new_password != $confirm_password)showmsg('两次输入的密码不一致');
		$user_info = member_info($userid);

		if (($user_info && (!empty($code) && md5($user_info['userid'] . $CFG['crypt'] . $user_info['registertime']) == $code))) {
			$password = MD5($new_password);
			$sql = "UPDATE {$table}member SET password='$password' WHERE userid='$userid' ";
			$query = $db->query($sql);

			if($CFG['uc']) {
				$result = uc_call("uc_user_edit", array($username, $old_password, $new_password, ''));
				if($result == -1)showmsg('旧密码不正确');
			}
			showmsg('密码修改成功', 'index.php');
		} else {
			showmsg('密码修改失败', 'index.php');
		}
	break;

	case 'gold':
		$userinfo = member_info($_userid);
		$seo['title'] = "购买信息币";
		include template('gold');
	break;

	case 'act_gold':
		$type = $_POST['type'];
		$number = $type == 'money2gold' ? intval($_POST['m_number']) : intval($_POST['c_number']);

		if($number <= 0)showmsg('数量必须大于0');
		$userinfo = member_info($_userid);
		$_credit = $number * $CFG['credit2gold'];
		$_money = $number * $CFG['money2gold'];

		if($type == 'money2gold') {
			if($_money > $userinfo['money']) showmsg('您的资金不足以支付此次购买');
			money_diff($_username, $_money, $type);
		} else {
			if($_credit > $userinfo['credit']) showmsg('您的积分不足以支付此次购买');
			credit_diff($_username, $_credit, $type);
		}
		gold_add($_username, $number, $type);

		showmsg('购买信息币成功' , 'member.php?act=gold');
	break;

	case 'credit_rule':
		$user_info = member_info($_userid);
		$seo['title'] = '积分规则';
		include template('credit_rule');
	break;

	case 'check_credit2gold':
		$json = new Services_JSON;
		$number = intval($_REQUEST['number']);
		$sql = "select credit from {$table}member where userid='$_userid'";
		$user_credit = $db->getOne($sql);
		$pay_credit = $number * $CFG['credit2gold'];
		$data = $pay_credit > $user_credit ? '0' : '1';
		echo $json->encode($data);
	break;

	case 'check_money2gold':
		$json = new Services_JSON;
		$number = intval($_REQUEST['number']);
		$sql = "select money from {$table}member where userid='$_userid'";
		$user_money = $db->getOne($sql);
		$pay_money = $number * $CFG['money2gold'];
		$data = $pay_money > $user_money ? '0' : '1';
		echo $json->encode($data);
	break;

	case 'check_info_gold':
		$json = new Services_JSON;
		extract($_REQUEST);
		$m_gold = $db->getOne("select gold from {$table}member where userid='$_userid' ");
		$data['kou'] = $CFG['info_top_gold'] * intval($number);
		$data['gold'] = $m_gold - $data['kou'];
		$data=$json->encode($data);
		echo $data;
	break;
	
	case 'info':
		$page = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
		$size = !empty($CFG['pagesize']) && intval($CFG['pagesize']) > 0 ? intval($CFG['pagesize']) : 20;
		$sql = "SELECT COUNT(*) FROM {$table}info WHERE userid='$_userid'";
		$count = $db->getOne($sql);
		$max_page = ($count> 0) ? ceil($count / $size) : 1;
		if($page>$max_page)$page = $max_page;
		$pager['search'] = array('act' => 'info');
		$pager = get_pager('member.php', $pager['search'], $count, $page, $size);
		$sql = "SELECT i.*,a.areaname FROM {$table}info as i left join {$table}area as a on a.areaid=i.areaid WHERE userid='$_userid' ORDER BY id desc LIMIT $pager[start],$pager[size]";
		$res = $db->query($sql);
		$infos = array();
		while($row = $db->fetchRow($res)) {
			$row['title']    = cut_str($row['title'],'18');
			$row['postdate'] = date('y年m月d日', $row['postdate']);
			$row['lastdate'] = enddate($row['enddate']);
			$row['is_pro']   = $row['is_pro']>=time() ? '是' : '否';
			$row['is_top']   = $row['is_top']>=time() ? '是' : '否';
			$row['is_check'] = $row['is_check']=='1' ? '是' : '否';
			$row['url']      = url_rewrite('view',array('vid'=>$row['id']));
			$infos[] = $row;
		}
		$seo['title'] = "我发布的信息";
		include template('member_info');
	break;

	case 'delinfo':
		$id = intval($_REQUEST['id']);
		if(empty($id)) showmsg('缺少参数！');
		$info = getInfo($id);
		if(empty($info)){showmsg('信息不存在','index.php');}
		checkInfoUser($id, trim($_REQUEST['password']));
		delInfo($id);

		showmsg('删除信息成功', $_SERVER['HTTP_REFERER']);
	break;

	case 'editinfo':
		$id = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}info WHERE id = '$id'";
		$info = $db->getRow($sql);
		if(empty($info)){showmsg('信息不存在','index.php');}
		checkInfoUser($id, trim($_REQUEST['password']));
		extract($info);
		$postdate = date('Y年m月d日', $postdate);
		$lastdate = round(($enddate-time())/(3600*24));
		$lastdate = $lastdate ? $lastdate : '30';
		
		if(!empty($mappoint)) {
			$mappoints = explode(',', $mappoint);
		} elseif(!empty($CFG['map'])) {
			$mappoints = explode(',', $CFG['map']);
		}
		$custom = cat_post_custom($catid,$id);
		$info_area = area_options($areaid);

		$seo['title'] = '修改信息 - Powered by Phpmps';
		include template('edit_info');
	break;

	case 'updateinfo':
		$id       = intval($_POST['id']);
		$title    = $_POST['title'] ? htmlspecialchars(trim($_POST['title'])) : '';
		$areaid   = $_POST['areaid'] ? intval($_POST['areaid']) : '';
		$enddate  = !empty($_POST['enddate']) ? (intval($_POST['enddate']*3600*24)) + time() : '0';
		$content  = $_POST['content'] ? htmlspecialchars(trim($_POST['content'])) : '';
		$linkman  = $_POST['linkman'] ? htmlspecialchars(trim($_POST['linkman'])) : '';
		$phone    = $_POST['phone'] ? trim($_POST['phone']) : '';
		$qq       = $_POST['qq'] ? intval($_POST['qq']) : '';
		$email    = $_POST['email'] ? htmlspecialchars(trim($_POST['email'])) : '';
		$address  = $_POST['address'] ? trim($_POST['address']) : '';
		$mappoint = $_POST['mappoint'] ? trim($_POST['mappoint']) : '';

		if(empty($title))showmsg("标题不能为空");
		if(empty($phone) && empty($qq) && empty($email))showmsg("电话、qq、email，必须填写一项");
		check_words(array($title,$content));

		$items = array(
			'areaid' => $areaid,
			'title' => $title,
			'content' => $content,
			'linkman' => $linkman,
			'email' => $email,
			'qq' => $qq,
			'phone' => $phone,
			'mappoint' => $mappoint,
			'address' => $address,
			'enddate' => $enddate
		);
		$res = editInfo($items, $_POST['cus_value'], $id);

		$res ? $msg="恭喜您，修改成功！" : $msg="抱歉修改失败，请与客服联系。";
		$link = "view.php?id=$id";
		showmsg($msg, $link);
	break;

	case 'report':
		$info     = intval($_REQUEST['id']);
		$type     = intval($_REQUEST['types']);
		$ip       = get_ip();
		$postdate = time();
		
		$yes = $db->getOne("SELECT COUNT(*) FROM {$table}report WHERE info='$info' AND ip='$ip'");
		if($yes) showmsg('您已经举报过了');

		$db->query("INSERT INTO {$table}report (info,type,ip,postdate) VALUES ('$info','$type','$ip','$postdate')");
		showmsg('举报成功,谢谢您的参与！', trim(htmlspecialchars($_SERVER['HTTP_REFERER'])));
	break;

	case 'comment':
		if(!$CFG['visitor_comment'] && empty($_userid)) showmsg('游客不允许发布评论，请登陆后再发布。');
		$infoid = intval($_POST['id']);
		$userid = $_userid;
		$username = $_username;
		$content = htmlspecialchars(trim($_POST['content']));
		$checkcode = trim($_POST['checkcode']);
		if(empty($infoid)) {
			showmsg('缺少信息编号');
		}
		$ip = get_ip();
		$postarea = getPostArea($ip);
		onlyarea($postarea);
		if(empty($content))showmsg('请填写评论内容');
		if(empty($checkcode))showmsg('请填写验证码');
		check_code($checkcode);
		check_words(array($content));

		$postdate = time();
		$check = $CFG['comment_check'] == '0' ? '1' : '0' ;
		$sql = "INSERT INTO {$table}comment (infoid,userid,username,content,postdate,is_check,ip) VALUES ('$infoid','$userid','$username','$content','$postdate','$check','$ip')";
		$res = $db->query($sql);

		if($_username) {
			$credit_count = $db->getOne("select count(*) from {$table}pay_exchange where username='$_username' and addtime>".mktime(0,0,0)." and note='post_comment_credit' ");
			if($credit_count < $CFG['max_comment_credit']) {
				if(!empty($CFG['post_comment_credit']))credit_add($_username, $CFG['post_comment_credit'],'post_comment_credit');
			}
		}
		if($CFG['comment_check'] == '1')$msg = "<br>评论审核后才能显示";
		$link = "view.php?id=$infoid";
		showmsg("发表评论成功 $msg", $link);
	break;

	case 'info_comment':
		$page = empty($_REQUEST['page'])? 1 : intval($_REQUEST['page']);
		$count = $db->getOne("SELECT COUNT(*) FROM {$table}comment where userid='$_userid'");
		$pager = get_pager('member.php',array('act'=>'info_comment'),$count,$page,'20');
		$sql = "SELECT * FROM {$table}comment where userid='$_userid' ORDER BY id DESC LIMIT $pager[start],$pager[size]";
		$res = $db->query($sql);
		$comments = array();
		while($row=$db->fetchRow($res)) {
			$row['postdate'] = date('Y-m-d', $row['postdate']);
			$row['is_check'] = $row['is_check'] == '1' ? '是' : '否' ;
			$row['title']    = cut_str($row['content'], 15);
			$comments[] = $row;
		}
		$seo['title'] = "我发表的信息评论列表";
	    include template('member_info_comment');
	break;

	case 'delete':
		$id = is_array($_REQUEST['id']) ? join(',', $_REQUEST['id']) : intval($_REQUEST['id']);
		if(empty($id))showmsg('没有选择记录');
		$db->query("DELETE FROM {$table}comment WHERE id IN ($id)");
		showmsg('删除成功', 'member.php?act=info_comment');
	break;

	case 'refer':
		$id = intval($_REQUEST['id']);
		$infouser = $db->getOne("select userid from {$table}info where id='$id' ");
		if($infouser != $_userid)showmsg('此信息不是您的账号发布的，无法操作');

		if(!empty($_POST['submit'])) {
			gold_diff($_username, $CFG['info_refer_gold'], 'info_refer');
			$db->query("update {$table}info set postdate=".time()." where id='$id' ");
			$url = url_rewrite('category', array('cid'=> $info['catid']));
			showmsg('信息更新成功', $url);
		} else {
			$seo['title'] = '一键刷新信息';
			$user_info = member_info($_userid);
			$info = $db->getRow("select * from {$table}info where id='$id'");
			include template('member_info_refer');
		}
	break;

	case 'top':
		$id = intval($_REQUEST['id']);
		$infouser = $db->getOne("select userid from {$table}info where id='$id' ");
		if($infouser != $_userid)showmsg('此信息不是您的账号发布的，无法操作');

		if(!empty($_POST['submit'])) {
			gold_diff($_username, $CFG['info_top_gold'], 'info_top');
			$is_top = intval($_POST['number'])*3600*24+time();
			$db->query("update {$table}info set is_top='$is_top',top_type='$_POST[is_top]' where id='$id' ");

			if($_POST['is_top']=='1') {
				$catinfo = get_cat_info($info['catid']);
				$catid = $catinfo['parentid'];
			} else {
				$catid = $info['catid'];
			}
			$url = url_rewrite('category', array('cid'=> $catid));
			showmsg('信息置顶成功', $url);
		} else {
			$seo['title'] = '信息置顶';
			$user_info = member_info($_userid);
			$info = $db->getRow("select * from {$table}info where id='$id'");
			$is_top = $info['is_top'];
			if($is_top>time())showmsg('此条信息已置顶');
			include template('member_info_top');
		}
	break;
	
	case 'send_info_mail':
		include PHPMPS_ROOT.'include/mail.inc.php';
		extract($_REQUEST);
		$email = decrypt($email, $CFG['crypt']);
		$content = $CFG['webname'].'代发,请勿回复。<br />'.$content;
		if (sendmail($email, $title, $content)) {
			showmsg('发送成功', $_SERVER['HTTP_REFERER']);
		} else {
			showmsg('发送失败', $_SERVER['HTTP_REFERER']);
		}
	break;

	case 'com_comment':
		$comid  = intval($_POST['comid']);
		$username = $_SESSION['username'];
		$content = htmlspecialchars(trim($_POST['content']));
		$checkcode = trim($_POST['checkcode']);
		if(empty($comid)) {
			header("Location: ./\n");
			exit;
		}
		require_once PHPMPS_ROOT . 'include/ip.class.php';
		$ip = get_ip();
		$cha = new ip();
		$address = $cha->getaddress($ip);
		$postarea = $address["area1"].$address["area2"];
		onlyarea($postarea);
		if(empty($content))showmsg('请填写评论内容');
		if(empty($checkcode))showmsg('请填写验证码');

		check_code($checkcode);
		check_words($who=array('content'));

		$postdate = time();
		$ip = get_ip();
		$check = $CFG['comment_check'] == '0' ? '1' : '0' ;
		$sql = "INSERT INTO {$table}com_comment (comid,userid,username,content,postdate,is_check,ip) VALUES ('$comid','$userid','$username','$content','$postdate','$check','$ip')";
		$res = $db->query($sql);
		if($CFG['comment_check'] == '1')$msg = "<br>评论审核后才能显示";

		showmsg("发表评论成功", $_SERVER['HTTP_REFERER']);
	break;

	case 'com':
		$page = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
		$size=20;
		$sql = "SELECT COUNT(*) FROM {$table}com WHERE userid='$_userid'";
		$count = $db->getOne($sql);
		$max_page = ($count> 0) ? ceil($count / $size) : 1;
		if($page>$max_page)$page = $max_page;
		$pager['search'] = array('act' => 'com');
		$pager = get_pager('member.php', $pager['search'], $count, $page, $size);
		$sql = "SELECT i.*,a.areaname FROM {$table}com as i left join {$table}area as a on a.areaid=i.areaid WHERE userid='$_userid' ORDER BY comid desc,postdate desc LIMIT $pager[start],$pager[size]";
		$res = $db->query($sql);
		$coms = array();
		while($row = $db->fetchRow($res)) {
			$row['comname']  = cut_str($row['comname'],'18');
			$row['postdate'] = date('y-m-d', $row['postdate']);
			$row['is_check'] = $row['is_check']=='1' ? '是' : '否';
			$row['url']      = url_rewrite('com',array('act'=>'view','comid'=>$row['comid']));
			$coms[] = $row;
		}
		$seo['title'] = "我发布的企业黄页信息";
		include template('member_com','com');
	break;

	case 'editcom':
		$comid = intval($_GET['id']);
		$sql = "SELECT c.*,cc.catname FROM {$table}com as c left join {$table}com_cat as cc on c.catid=cc.catid WHERE comid = '$comid'";
		$com = $db->getRow($sql);
		$com['comuid'] = $com['userid'];
		unset($com['userid']);
		if(empty($com))showmsg('信息不存在','index.php');
		if($com['comuid']!=$_userid)showmsg('信息不是您发布的，不能修改');
		extract($com);
		$postdate = date('Y-m-d', $postdate);
		$thumb    = PHPMPS_PATH.$thumb;

		if(!empty($mappoint)) {
			$mappoints = explode(',', $mappoint);
		}
		$com_area = area_options($areaid);
		$seo['title'] = '修改企业黄页信息';
		include template('member_editcom','com');
	break;

	case 'updatecom':
		$comid    = intval($_POST['id']);
		$comname  = $_POST['comname'] ? htmlspecialchars(trim($_POST['comname'])) : '';
		$areaid   = $_POST['areaid'] ? intval($_POST['areaid']) : '';
		$introduce  = $_POST['introduce'] ? htmlspecialchars(trim($_POST['introduce'])) : '';
		$description = cut_str($introduce,30);
		$linkman  = $_POST['linkman'] ? htmlspecialchars(trim($_POST['linkman'])) : '';
		$phone    = $_POST['phone'] ? trim($_POST['phone']) : '';
		$qq       = $_POST['qq'] ? intval($_POST['qq']) : '';
		$email    = $_POST['email'] ? htmlspecialchars(trim($_POST['email'])) : '';
		$address  = $_POST['address'] ? trim($_POST['address']) : '';
		$mappoint = $_POST['mappoint'] ? trim($_POST['mappoint']) : '';
		$hours     = $_POST['hours'] ? htmlspecialchars(trim($_POST['hours'])) : '';
		$fax       = trim($_POST['fax']);

		if(empty($comname))showmsg("标题不能为空");
		if(empty($introduce))showmsg("内容不能为空");
		if(empty($phone) && empty($qq) && empty($email))showmsg("电话、qq、email，必须填写一项");
		
		check_words($who=array('comname','content'));

		if(!empty($_FILES['thumb']['name']))
		{
			$sql = "SELECT thumb FROM {$table}com WHERE comid IN ($comid)";
			$image = $db->getAll($sql);
			foreach((array)$image AS $val) {
				if($val['thumb'] != '' && is_file(PHPMPS_ROOT.$val['thumb'])) {
					@unlink(PHPMPS_ROOT . $val['thumb']);
				}
			}
			$alled = array('png','jpg','gif','jpeg');
			$exname = strtolower(trim(substr(strrchr($_FILES['thumb']['name'], '.'), 1)));
			if(checkupfile($_FILES['thumb']['tmp_name']) && $_FILES['thumb']['tmp_name'] != 'none' && $_FILES['thumb']['tmp_name'] && $_FILES['thumb']['name'] && $_FILES['thumb']['size']<'523298' && in_array($exname,$alled))
			{
				$thumb_name = $comid.'_thumb'. '.' . end(explode('.', $_FILES['thumb']['name']));
				$dir = PHPMPS_ROOT . 'data/com/thumb/';
				if(!is_dir($dir)) {
					if(@mkdir(rtrim($dir,'/'), 0777))@chmod($dir, 0777);
				}
				$to = $dir.'/'. $thumb_name;
				CreateSmallImage( $_FILES['thumb']['tmp_name'], $to, 200, 80);
				$image = 'data/com/thumb/'. $thumb_name;
				$sql = "update {$table}com set thumb='$image' where comid='$comid' ";
				$db->query($sql);
			}
		}
		$sql = "UPDATE {$table}com SET
				areaid='$areaid',
				comname='$comname',
				introduce='$introduce',
				description='$description',
				linkman='$linkman',
				email='$email',
				qq='$qq',
				phone='$phone',
				mappoint='$mappoint',
				address='$address',
				fax='$fax',
				hours='$hours'
				where comid = '$comid' ";
		$res = $db->query($sql);
		
		$msg="恭喜您，修改成功！";
		$link = url_rewrite('com',array('act'=>'view', 'comid'=>$comid));
		showmsg($msg, $link);
	break;

	case 'delcom':
		$comid = trim($_REQUEST['id']);
		$sql = "select userid from {$table}com where comid='$comid' ";
		$comuserid = $db->getOne($sql);
		if($comuserid!=$_userid)showmsg('此信息不是您发布的，您不能修改');
		
		$sql = "SELECT thumb FROM {$table}com WHERE comid IN ($comid)";
		$image = $db->getOne($sql);
		if($image != '' && is_file(PHPMPS_ROOT.$image)) {
			@unlink(PHPMPS_ROOT.$image);
		}

		$sql = "SELECT path FROM {$table}com_image WHERE comid IN ($comid)";
		$image = $db->getAll($sql);
		foreach((array)$image AS $val) {
			if($val[path] != '' && is_file(PHPMPS_ROOT.$val[path])) {
				@unlink(PHPMPS_ROOT.$val[path]);
			}
		}

		$db->query("DELETE FROM {$table}com_image WHERE comid IN ($comid)");
		$db->query("DELETE FROM {$table}com WHERE comid IN ($comid)");

		showmsg('删除信息成功',$_SERVER['HTTP_REFERER']);
	break;

	case 'avatar':
		if(!$CFG['uc']) showmsg('系统没有整合Ucenter，不能使用此功能');
		include PHPMPS_ROOT.'include/uc.inc.php';
		if(!$_userid) showmsg('请先登录', 'member.php?act=login');

		$uid = $db->getone("select uid from {$table}member where userid='$_userid' ");
		$uc_html = uc_call("uc_avatar",  array($uid));
		$seo['title'] = '修改头像';
		include template('member_uc_avatar');
	break;
	
	case 'send_check_email':
		if($_POST) 
		{
			$email = trim($_POST['email']);
			$user_info = member_info($_userid);
			$code = md5($user_info['userid'] . $CFG['crypt'] . $user_info['registertime']);
			$reset_email = $CFG['weburl'].'/member.php?act=check_email&userid='.$_userid.'&code=' . $code;
			
			$send_date = date('Y-m-d', time());
			$content = "{$username}您好！<br><br>请点击以下链接(或者复制到您的浏览器):<br><br><a href=".$reset_email." target=\"_blank\">".$reset_email."</a><br><br>以进行您的邮件验证！<br><br>".$send_date;

			$code = md5($user_info['userid'] . $CFG['crypt'] . $user_info['registertime']);
			include PHPMPS_ROOT.'include/mail.inc.php';
			if (sendmail($email, $CFG['webname'].'-邮件验证', $content)) {
				showmsg('发送成功,请登录邮箱进行验证' , 'member.php?act=send_check_email');
			} else {
				showmsg('发送失败' , 'member.php?act=send_check_email');
			}
		} else {
			$userinfo = member_info($_userid);
			$seo['title'] = '验证邮件';
			include template('member_check_email');
		}
	break;

	case 'check_email':
		$code = isset($_GET['code']) ? trim($_GET['code'])  : '';
		$user_info = member_info(intval($_REQUEST['userid']));

		if ($user_info && (!empty($code) && md5($user_info['userid'] . $CFG['crypt'] . $user_info['registertime']) == $code)) {
			$sql = "update {$table}member set status='1' where userid='$_userid' ";
			$query = $db->query($sql);
			showmsg('邮件验证成功', 'member.php');
		} else {
			showmsg('邮件验证失败', '?send_check_email');
		}
	break;
}
?>