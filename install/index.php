<?php
//安装文件

define('IN_PHPMPS', true);
$charset = 'utf-8';
$dbcharset = 'utf8';
require_once 'common.php';

if(file_exists(PHPMPS_ROOT . 'data/install.lock')) {
	die('系统已安装，如果要重新安装，请删除 date/install.lock 这个文件。');
}

//初始化step
$_REQUEST['step'] = $_REQUEST['step'] ? $_REQUEST['step'] : 'license' ;

switch($_REQUEST['step'])
{
	case 'license';
		include tpl('license');
	break;

	case '1':
		require_once 'dirs.php';
		foreach ($dirs AS $key=>$dir)
		{
			$message[$key]['name'] = $dir;
			if(!is_writable(PHPMPS_ROOT . $dir))
			{
				$next = 'no';
				$message[$key]['write'] = "不可写";
			}
			else
			{
				$message[$key]['write'] = "可写";
			}
		}
		include tpl('1');
	break;

	case '2':
		$code = MD5(random('6'));
		$path = get_url();
		include tpl('2');
	break;

	case '3':
		$db_host  = trim($_POST['db_host']);
		$db_user  = trim($_POST['db_user']);
		$db_pass  = trim($_POST['db_pass']);
		$db_name  = trim($_POST['db_name']);
		$db_table = trim($_POST['db_table']);

		$admin      = trim($_POST['admin']);
		$password   = trim($_POST['password']);
		$repassword = trim($_POST['repassword']);
		$email      = trim($_POST['email']);
		$crypt      = trim($_POST['crypt']);
		$path       = trim($_POST['path']);

		if(empty($db_host))die("未填写数据库服务器地址。");
		if(empty($db_user))die("未填写数据库服务器用户名。");
		if(empty($db_name))die("未填写数据库名称。");
		if(empty($db_table))die("未填写数据库表前缀。");

		if(empty($admin))die("未填写管理员帐号。");
		if(empty($password))die("未填写管理员密码。");
		if(empty($repassword))die("未填写重复密码。");
		if($password != $password)die("两次输入的密码不一致。");
		if(empty($email))die("未填写电子邮件。");
		if(!is_email($email))die("电子邮件格式不正确。");
		if(empty($crypt))die("未填写加密字符串。");
		
		//检测数据库是否存在，不存在则创建
		$conn = @mysql_connect($db_host, $db_user, $db_pass);
		if($conn===false)die("无法连接数据库,请检查相关参数是否正确。");
		mysql_connect($db_host, $db_user, $db_pass);
		$yes = mysql_select_db($db_name);
		$mysql_version = mysql_get_server_info();
		if($yes===false)
		{
			$sql = $mysql_version >= '4.1' ? "CREATE DATABASE $db_name DEFAULT CHARACTER SET $dbcharset" : "CREATE DATABASE $db_name";
			if (mysql_query($sql, $conn) === false)
			{
				die("无法创建数据库,请检查相关参数是否正确。");
			}
		}
		@mysql_close($conn);

		//写入config.inc.php文件
		$files = '<?'."php\n";
		$files .= "\$db_host   = \"$db_host\";\n\n";
		$files .= "\$db_name   = \"$db_name\";\n\n";
		$files .= "\$db_user   = \"$db_user\";\n\n";
		$files .= "\$db_pass   = \"$db_pass\";\n\n";
		$files .= "\$table     = \"$db_table\";\n\n";
		$files .= "\$charset   = \"$charset\";\n\n";
		$files .= "\$dbcharset = \"$dbcharset\";\n";
		$files .= '?>';

		$file = @fopen(PHPMPS_ROOT . 'data/config.php', 'wb+');
		if(!$file)
		{
			die('无法打开文件');
		}
		if(!@fwrite($file, trim($files)))
		{
			die('无法写入文件');
		}
		@fclose($file);

		require PHPMPS_ROOT . 'data/config.php';
		require PHPMPS_ROOT . 'include/mysql.class.php';
		$db = new mysql($db_host, $db_user, $db_pass, $db_name, $dbcharset);
		$db_host = $db_user = $db_pass = $db_name = NULL;

		//导入phpmps.sql
		import_sql('phpmps.sql', $db_table);

		//建立管理帐户
		$password = MD5($password);
		$sql = "INSERT INTO {$table}admin (username,password,email,is_admin) VALUES ('$admin','$password','$email','1')";
		$db->query($sql);
		$sql = "UPDATE {$table}config SET value='$crypt' where setname='crypt' ";
		$db->query($sql);
		$sql = "UPDATE {$table}config SET value='$path' where setname='weburl' ";
		$db->query($sql);
		//生成安装锁定文件
		write_lock();

		include tpl('3');
	break;

	case 'check_db':
		$db_host = trim($_REQUEST['db_host']);
		$db_user = trim($_REQUEST['db_user']);
		$db_pass = trim($_REQUEST['db_pass']);
		$db_name = trim($_REQUEST['db_name']);
		$conn = mysql_connect($db_host, $db_user, $db_pass);
		$yes = mysql_select_db($db_name);
		@mysql_close($conn);

		$json = new Services_JSON;
		$s = $json->encode($yes);
		exit($s);
	break;

}
?>