<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'index' ;

switch ($_REQUEST['act'])
{
	case 'index':
		include tpl('index');
	break;

	case 'left':
		include tpl('left');
	break;

	case 'right':
		$sql = "SELECT * FROM {$table}admin WHERE userid='$_SESSION[adminid]'";
		$row = $db->getRow($sql); 
		$admin['lastip']    = $row['lastip'];
		$admin['lastlogin'] = date('Y年m月d日', $row['lastlogin']);
		
		$info_num    = $db->getOne("SELECT COUNT(*) FROM {$table}info");
		$report_num  = $db->getOne("SELECT COUNT(*) FROM {$table}report");
		$member_num  = $db->getOne("SELECT COUNT(*) FROM {$table}member");
		$comment_num = $db->getOne("SELECT COUNT(*) FROM {$table}comment");
		$com_num     = $db->getOne("SELECT COUNT(*) FROM {$table}com");
		$article_num = $db->getOne("SELECT COUNT(*) FROM {$table}article");
		include_once PHPMPS_ROOT . 'include/version.inc.php';
		if(file_exists('../install'))$install='1';

		include tpl('right');
	break;

	case 'clear_caches':
		$phpchche = clear_caches('phpcache');
		$compiled = clear_caches('compiled');
		$sqlcache = clear_caches('sqlcache');
		if($phpcache && $compiled && $sqlcache) echo '1';
		show('清理缓存成功');
	break;

	case 'phpinfo':
		phpinfo();
	break;

	case 'test_mail':
		include_once PHPMPS_ROOT . 'include/json.class.php';
		include_once PHPMPS_ROOT . 'include/mail.inc.php';
		$mailfrom = trim($_REQUEST['mailfrom']);
		$mailto = trim($_REQUEST['mailto']);
		if($mailfrom!='' && !is_email($mailfrom))exit;
		$title = '邮件服务器配置测试邮件';
		$content = '邮件服务器配置测试邮件'; 
		if(!is_email(trim($mailto)))exit;
		
		if(sendmail($mailto,$title,stripslashes($content),$mailfrom)) {
			$data = '1';
		} else {
			$data = '0';
		}
		$json = new Services_JSON;
		$data=$json->encode($data);
		echo $data;
	break;
}
?>