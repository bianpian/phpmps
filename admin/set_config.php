<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('config');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch($_REQUEST['act'])
{
	case 'list':
		$CFG = '';
		$sql = "select setname,value from {$table}config";
		$res = $db->query($sql);
		while($row=$db->fetchRow($res)) {
			$CFG[$row['setname']] = $row['value'];
		}
		$tpl_dir = tpl_dir();
		include tpl('set_config');
	break;

	case 'set_config':
		$_POST['webname']	    = $_POST['webname'] ? trim($_POST['webname']) : '';
		$_POST['weburl']		= trim($_POST['weburl']);
		$_POST['icp']			= trim($_POST['icp']);
		$_POST['qq']			= $_POST['qq'] ? trim($_POST['qq']) : '';
		$_POST['tplname']		= trim($_POST['tplname']);
		$_POST['crypt']		    = trim($_POST['crypt']);
		$_POST['post_check']    = intval($_POST['post_check']);
		$_POST['rewrite']		= intval($_POST['rewrite']);
		$_POST['maxpost']		= intval($_POST['maxpost']);
		$_POST['banwords']	    = trim($_POST['banwords']);
		$_POST['annouce']		= trim($_POST['annouce']);
		$_POST['description']   = trim($_POST['description']);
		$_POST['keywords']      = trim($_POST['keywords']);
		$_POST['copyright']     = trim($_POST['copyright']);
		$_POST['count']         = trim($_POST['count']);
		$_POST['comment_check'] = trim($_POST['comment_check']);
		$_POST['onlyarea']      = trim($_POST['onlyarea']);
		$_POST['del_m_info']    = intval($_POST['del_m_info']);
		$_POST['del_m_comment'] = intval($_POST['del_m_comment']);
		$_POST['pagesize']      = intval($_POST['pagesize']);
		$_POST['postfile']      = trim($_POST['postfile']);
		$_POST['uc']            = intval($_POST['uc']);
		$_POST['uc_dbhost']     = trim($_POST['uc_dbhost']);
		$_POST['uc_dbuser']     = trim($_POST['uc_dbuser']);
		$_POST['uc_dbname']     = trim($_POST['uc_dbname']);
		$_POST['uc_dbpwd']      = trim($_POST['uc_dbpwd']);
		$_POST['uc_dbpre']      = trim($_POST['uc_dbpre']);
		$_POST['uc_key']        = trim($_POST['uc_key']);
		$_POST['uc_appid']      = intval($_POST['uc_appid']);
		$_POST['uc_api']        = trim($_POST['uc_api']);
		$_POST['uc_charset']    = trim($_POST['uc_charset']);
		$_POST['expired_view']  = intval($_POST['expired_view']);
		$_POST['visitor_post']  = intval($_POST['visitor_post']);
		$_POST['visitor_view']  = intval($_POST['visitor_view']);
		$_POST['visitor_comment'] = intval($_POST['visitor_comment']);
		$_POST['closesystem']   = intval($_POST['closesystem']);
		$_POST['close_tips']    = trim($_POST['close_tips']);
		$_POST['sendmailtype']  = trim($_POST['sendmailtype']);
		$_POST['smtphost']      = trim($_POST['smtphost']);
		$_POST['smtpuser']      = trim($_POST['smtpuser']);
		$_POST['smtppass']      = trim($_POST['smtppass']);
		$_POST['smtpport']      = trim($_POST['smtpport']);
		$_POST['info_top_gold'] = intval($_POST['info_top_gold']);
		$_POST['com_pagesize'] = intval($_POST['com_pagesize']);
		
		$_POST['info_refer_gold']  = intval($_POST['info_refer_gold']);
		$_POST['login_credit']     = intval($_POST['login_credit']);
		$_POST['register_credit']  = intval($_POST['register_credit']);
		$_POST['post_info_credit'] = intval($_POST['post_info_credit']);
		$_POST['post_comment_credit'] = intval($_POST['post_comment_credit']);
		$_POST['credit2gold']		  = intval($_POST['credit2gold']);
		$_POST['money2gold']		  = intval($_POST['money2gold']);

		$_POST['max_login_credit']   = intval($_POST['max_login_credit']);
		$_POST['max_comment_credit'] = intval($_POST['max_comment_credit']);
		$_POST['max_info_credit']	 = intval($_POST['max_info_credit']);
		
		$_POST['qqun']	= trim($_POST['qqun']);
		$_POST['email']	= trim($_POST['email']);
		$_POST['phone']	= trim($_POST['phone']);
		$_POST['close_register']	= intval($_POST['close_register']);
		$_POST['reg_check']	= intval($_POST['reg_check']);
		$_POST['com_thumbwidth'] = floatval($_POST['com_thumbwidth']);
		$_POST['com_thumbheight']	= floatval($_POST['com_thumbheight']);
		
		if($_POST['weburl']=='' || $_POST['weburl']=="http://www.phpmps.com") {
			$_POST['weburl'] = get_url();
		}
		unset($_POST['act']);
		unset($_POST['submit']);
		foreach($_POST as $key=>$val) {
			$data = $db->getone("SELECT * FROM {$table}config WHERE setname='$key'");
			if($data) {
				$sql = "UPDATE {$table}config SET value = '$val' WHERE setname = '$key' ";
			} else {
				$sql = "INSERT INTO {$table}config (setname,value) VALUES ('$key','$val') ";
			}
			$res = $db->query($sql);
			$res ? $msg.='' : $msg.='1';
		}
		empty($msg) ? $msg = "修改配制成功": $msg = "修改配制失败";
		admin_log("$msg");
		clear_caches('phpcache');
		$link = "set_config.php";
		show($msg, $link);
	break;
}

//--------function--------
function tpl_dir()
{
	$datadir = opendir(PHPMPS_ROOT . "templates");
	while($file = readdir($datadir)) {
		if($file!='.' && $file!='..' && $file!="index.htm"){
			$files[] = $file;
		}
	}
	closedir($datadir);
	return $files;
}
?>