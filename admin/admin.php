<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

//初始化act操作
$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

if($_REQUEST['act'] != 'modify' && $_REQUEST['act'] != 'repass')chkadmin('admin');

switch ($_REQUEST['act'])
{
	case 'list':
		$sql = "SELECT * FROM {$table}admin ORDER BY userid";
		$res = $db->query($sql);
		$admin = array();
		while($row = $db->fetchRow($res)){
			$admin[] = $row;
		}
		include tpl('list_admin');
	break;

	case 'add':
		include tpl('add_admin');
	break;

	case 'insert':
		$username = trim($_POST['username']);
		$email    = trim($_POST['email']);
		$purview  = is_array($_POST['purview']) ? implode(",", $_POST['purview']) : '';

		if(empty($username))show('请输入用户名');
		if(!empty($username)) {
			$sql = "select count(*) from {$table}admin where username = '$username'";
			if($db->getOne($sql))show('已经存在此用户名，请重新输入！');
		}
		if(empty($_POST['password']))show('请输入密码');
		if(empty($_POST['repass']))show('请输入重复密码');
		if($_POST['password'] <> $_POST['repass'])show('两次输入的密码不一致');
		
		if(empty($email))show('请输入邮箱');
		if(!empty($email)) {
			if(!is_email($email))show('邮件格式不正确');
			$sql = "select count(*) from {$table}admin where email = '$email'";
			if($db->getOne($sql))show('已经存在此邮件，请重新输入！');
		}
		
		$password = MD5($_POST['password']);
		$sql = "INSERT INTO {$table}admin (username,password,email,purview) VALUES ('$username','$password','$email','$purview')";
		$res = $db->query($sql);

		$msg = $res ? '添加管理员成功' : '添加管理员失败';
		admin_log('添加管理员 $username 成功');
		$link = 'admin.php?act=add';
		show('添加管理员成功', $link);
	break;

	case 'edit':
		$userid = intval($_REQUEST['id']);
		$sql = "select * from {$table}admin where userid = '$userid'";
		$admin = $db->getRow($sql);

		$purview = explode(',',$admin['purview']);
		include tpl('edit_admin');
	break;

	case 'update':
		$userid  = trim($_GET['id']);
		$email   = trim($_POST['email']);
		$purview = is_array($_POST['purview']) ? implode(",", $_POST['purview']) : '';

		if(!empty($_POST['password']) && !empty($_POST['repass'])){	
			if($_POST['password'] <> $_POST['repass'])show('两次输入的密码不一致');
			$password = MD5($_POST['password']);
			$pass = "password = '$password',";
		}
		
		if(empty($email))show('邮箱不能为空');
		if(!empty($_POST['email'])){
			if(!is_email($email))show('邮件格式不正确');
			$sql = "select count(*) from {$table}admin where email = '$email' and userid <> '$_GET[id]' ";
			if($db->getOne($sql))show('已经存在此email，请重新输入！');
		}
		
		$sql = "update {$table}admin set 
		".$pass."
		email = '$email',
		purview = '$purview'
		where userid = '$userid' ";
		$res = $db->query($sql);

		$msg = $res ? '编辑管理员成功' : '编辑管理员失败';
		admin_log('编辑管理员 $username 成功');

		$link = 'admin.php?act=list';
		show("编辑管理员成功", $link);
	break;

	case 'modify':
		include tpl('modify');
	break;

	case 'repass':
		if(empty($_REQUEST[password]))show("请输入密码");
		if(empty($_REQUEST[repassword]))$msg .= "请输入重复密码\n";
		if($_REQUEST[password] <> $_REQUEST[repassword])show("两次输入的密码不一致");
		
		$password = md5($_REQUEST[password]);
		$sql = "UPDATE {$table}admin SET password = '$password' WHERE userid = '$_SESSION[adminid]'";
		$res = $db->query($sql);
		admin_log("$_SESSION[adminid]修改密码成功");
		show('资料修改成功', 'admin.php');
	break;

	case 'delete':
		$userid = intval($_GET[id]);
		//check is_admin
		$sql = "select is_admin from {$table}admin where userid = '$userid' ";
		$is_admin = $db->getOne($sql);
		if($is_admin>0)show('初始管理员不能被删除');
		//get username
		$username = $db->getOne("select username from {$table}admin where userid = '$userid' ");
		//delete user
		$sql = "delete from {$table}admin where userid = '$userid' ";
		$res = $db->query($sql);
		$msg = $res ? '删除管理员 $username 成功' : '删除管理员 $username 成功';
		admin_log("删除管理员$username成功");
		$link = "admin.php?act=list";
		show("删除管理员$username成功", $link);
	break;
}
?>