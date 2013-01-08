<?php
define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';
if($CFG['uc'])require PHPMPS_ROOT . 'include/uc.inc.php';
chkadmin('member');
$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$here = "会员列表";
		$page = empty($_REQUEST['page'])? 1 : intval($_REQUEST['page']);
		$sql = "SELECT COUNT(*) FROM {$table}member";
		$count = $db->getOne($sql);
		$pager = get_pager('member.php',array('act'=>'list'),$count,$page,'20');
		
		$sql = "SELECT * FROM {$table}member ORDER BY lastlogintime DESC LIMIT $pager[start],$pager[size]"; 
		$res = $db->query($sql);
		$userinfo = array();
		while($row=$db->fetchRow($res)) {
			$row['username'] = cut_str($row['username'],20);
			$row['registertime']  = date('Y-m-d H:i:s',$row['registertime']);
			$row['lastlogintime'] = date('Y-m-d H:i:s',$row['lastlogintime']);
			$userinfo[] = $row;
		}
	    include tpl('list_member');
	break;

	case 'edit':
	    $userid = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}member WHERE userid = '$userid'";
		$userinfo = $db->getRow($sql);
		$here = "编辑会员信息";
		include tpl('edit_member');
	break;

	case 'update':
		$userid = $_REQUEST['id'] ? intval($_REQUEST['id']) : '';
		$username = $_REQUEST['username'] ? trim($_REQUEST['username']) : '';
		$password = $_REQUEST['password'] ? trim($_REQUEST['password']) : '';
		$repassword = $_REQUEST['repassword'] ? trim($_REQUEST['repassword']) : '';
		$email = $_REQUEST['email'] ? trim($_REQUEST['email']) : '';
		$status = $_REQUEST['status'] ? trim($_REQUEST['status']) : '0';

		if($password != $repassword)show('两次输入密码不相同');
		if($password && (strlen($password) < 5 || strlen($password) > 30))show("密码必须在5个至30个字符之间");
		if(empty($email))show('邮箱不能为空');
		if(!preg_match("/^[0-9a-zA-Z_-]+@[0-9a-zA-Z_-]+\.[0-9a-zA-Z_-]+$/",$email))show('邮箱格式错误');
		
		/* 验证邮件是否存在 */
		$sql = "select count(*) from {$table}member where email='$email' and userid<>'$userid' ";
		if($db->getOne($sql)>0)show('您所填写的邮件已经存在');
		
		if($password)$set[] = " password = '".MD5($password)."' ";
		$set[] = " email = '$email' ";
		$set[] = " status = '$status' ";
		if(!empty($set)) $set = join(',', $set);

		$res = $db->query("UPDATE {$table}member SET $set WHERE userid = '$userid'");
		
		if($CFG['uc']) {
			uc_call("uc_user_edit", array($username, '', $password, $email, '1'));
		}
		admin_log("修改会员 $username 信息成功");
		$link = "member.php?act=list";
		show("修改会员 $username 信息成功", $link);
	break;

	case 'batch':
		$userid = is_array($_REQUEST['id']) ? join(',',$_REQUEST['id']) : intval($_REQUEST['id']);
		if(empty($userid))show('没有选择记录');
		
		/* 
		 如果系统设置删除会员的同时删除所发布的信息，则删除会员所发布所有信息，包括信息的图片，信息的评论和信息的举报。
		 此设置在系统设置"删除会员是否同时删除会员发布的信息"。
		 */
		if($CFG['del_m_info'])
		{
			$sql = "SELECT id FROM {$table}info WHERE userid in ($userid) ";
			$infos = $db->getAll($sql);

			foreach($infos as $info)
			{
				//删除评论
				$db->query("DELETE FROM {$table}comment WHERE infoid = '$info[id]'");

				//删除所有图片
				$sql = "select * from {$table}info_image where infoid='$info[id]'";
				$res = $db->query($sql);
				while($row=$db->fetchrow($res)) {
					if($row['path'] != '' && is_file(PHPMPS_ROOT.$row['path']))
					@unlink(PHPMPS_ROOT.$row['path']);
				}

				//删除图片数据库记录
				$sql = "DELETE FROM {$table}info_image WHERE infoid = $info[id]";
				$db->query($sql);

				//删除附加属性
				$sql = "DELETE from {$table}cus_value WHERE infoid = $info[id]";
				$sql = $db->query($sql);
				
				//删除本信息
				$sql = "DELETE FROM {$table}info WHERE id = '$info[id]'";
				$db->query($sql);
			}
		}
		/*
		 * 删除用户的其他评论。
		 * 系统设置删除会员时删除此会员发表的评论的话，则删除评论。
		 */
		if($CFG['del_m_comment']) $db->query("delete from {$table}comment where userid in ($userid) ");
		
		/* 删除会员的数据库信息 */
	    $db->query("DELETE FROM {$table}member WHERE userid in ($userid) ");

		admin_log("删除会员 $userid 成功");
		$link = 'member.php?act=list';
		show("删除会员 $userid 成功", $link);
	break;
}
?>