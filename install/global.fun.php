<?php

/*
 * $Id: global.php 2008-8-1 9:12:02z happyboy $
 * -------------------------------------------
 * 网址：www.phpmps.com
 * -------------------------------------------
 * 这是一个自由软件。
*/

if (!defined('IN_PHPMPS'))
{
    die('Access Denied');
}

function str_len($str)
{
    $length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));

    if ($length)
    {
        return strlen($str) - $length + intval($length / 3) * 2;
    }
    else
    {
        return strlen($str);
    }
}

/*
名称: random
参数  $length 必选
作用: 生成随机字符串
*/
function random($length)
{
	$chars = '0123456789ABCDEFGHIJ0123456789KLMNOPQRSTJ0123456789UVWXYZ0123456789abcdefghijJ0123456789klmnopqrstJ0123456789uvwxyz0123456789';
	$max = strlen($chars);
	mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i ++)
    {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

function addslashes_deep($value)
{
	return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
}

function new_htmlspecialchars($value)
{
    $value = is_array($value) ? array_map('new_htmlspecialchars', $value) : htmlspecialchars($value,ENT_QUOTES);
	return $value;
}

//验证邮件
function is_email($email)
{
	return strlen($email) > 8 && preg_match("/^[-_+.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+([a-z]{2,4})|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email);
}

//模板函数
function tpl($file)
{
	$file = PHPMPS_ROOT.'install/template/'.$file.'.htm';
    return $file;
}

function write_lock()
{
    $file = @fopen(PHPMPS_ROOT . 'data/install.lock', 'wb+');
    if (!$file)
    {
        die('打开文件失败');
        return false;
    }
    if (!@fwrite($file, "PHPMPS_INSTALL"))
    {
        die('写入文件失败');
        return false;
    }
    @fclose($fp);
}

function mysql_ver($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function import_sql($sql,$table)
{
	global $db,$dbcharset;
	$fp  = fopen($sql, 'rb');
	$sql = fread($fp, filesize($sql));
	fclose($fp);
	$sql = str_replace("\r", "\n", str_replace(' `phpmps_', ' `'.$table, $sql));
	$sql = str_replace("\r", "\n", $sql);
	$sql = explode(";\n", trim($sql));
	foreach($sql as $sql)
	{
		$sql = trim($sql);
		if($sql)
		{
			if(substr($sql, 0, 12) == 'CREATE TABLE')
			{
				$db->query(mysql_ver($sql, $dbcharset));
			}
			else
			{
				$db->query($sql);
			}
		}
	}
	return true;
}

function get_url()
{
	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$php_domain = $_SERVER['SERVER_NAME'];
	$php_agent = $_SERVER['HTTP_USER_AGENT'];
	$php_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	$php_scheme = $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_reuri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	$php_port = $_SERVER['SERVER_PORT'] == '80' ? '' : ':'.$_SERVER['SERVER_PORT'];
	$host_url = $php_scheme . $php_domain . $php_port;
	$site_url = $host_url . substr($php_self, 0, strrpos($php_self, '/'));
	$site_url = str_replace('/install', '', $site_url);
	$site_url = str_replace('/admin', '', $site_url);
	return $site_url;
}
?>