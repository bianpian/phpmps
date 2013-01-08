<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';
require_once PHPMPS_ROOT . '/include/version.inc.php';
@set_time_limit(0);

chkadmin('database');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'exportdata' ;

switch($_REQUEST['act'])
{
	case 'exportdata':

		if($_REQUEST['submit'])
		{
			$fileid = isset($_REQUEST['fileid']) ? $_REQUEST['fileid'] : 1;
			$tables = $_REQUEST['tables'];
			$sizelimit = $_REQUEST['sizelimit'];
			$sqlcompat = $_REQUEST['sqlcompat'];
			$sqlcharset = $_REQUEST['sqlcharset'];

			if($fileid==1 && $tables) 
			{
				if(!isset($tables) || !is_array($tables)) showmessage('没有选择数据表');
				$random = mt_rand(1000, 9999);
				$cache_file = PHPMPS_ROOT . 'data/bakup/tables.php';
				$content = "<?php\r\n";
				$content .= "\$data = " . var_export($tables, true) . ";\r\n";
				$content .= "?>";
				file_put_contents($cache_file, $content, LOCK_EX);
			}
			else
			{
				$random = $_REQUEST['random'];
				include PHPMPS_ROOT . 'data/bakup/tables.php';
				$tables = $data;
			    if(!$tables) showmessage('没有选择数据表');
			}

			$dumpcharset = $sqlcharset ? $sqlcharset : str_replace('-', '',$dbcharset);

			$setnames = ($sqlcharset && $db->version() > '4.1' && (!$sqlcompat || $sqlcompat == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
			
			if($db->version() > '4.1')
			{
				if($sqlcharset)
				{
					$db->query("SET NAMES '".$sqlcharset."';\n\n");
				}
				if($sqlcompat == 'MYSQL40')
				{
					$db->query("SET SQL_MODE='MYSQL40'");
				}
				elseif($sqlcompat == 'MYSQL41')
				{
					$db->query("SET SQL_MODE=''");
				}
			}
			
			$sqldump = '';
			$tableid = isset($_REQUEST['tableid']) ? $_REQUEST['tableid'] - 1 : 0;
			$startfrom = isset($_REQUEST['startfrom']) ? intval($_REQUEST['startfrom']) : 0;
			$tablenumber = count($tables);
			
			for($i = $tableid; $i < $tablenumber && strlen($sqldump) < $sizelimit * 1000; $i++)
			{
				$sqldump .= sql_dumptable($tables[$i], $startfrom, strlen($sqldump));
				$startfrom = 0;
			}
			
			if(trim($sqldump))
			{
				$sqldump = "# phpmps bakfile\n# version:".$VERSION['version']."\n# time:".date('Y-m-d H:i:s')."\n# --------------------------------------------------------\n\n\n".$sqldump;
				$tableid = $i;
				$filename = date('Ymd').'_'.$random.'_'.$fileid.'.sql';
				$fileid++;

				$bakfile = PHPMPS_ROOT.'/data/bakup/'.$filename;

				if(!is_writable(PHPMPS_ROOT.'/data/bakup/')) showmessage('备份文件无法保存到服务器');

				file_put_contents($bakfile, $sqldump);
				@chmod($bakfile, 0777);

				show("备份文件 $filename 备份成功，程序将继续运行", 'database.php?act='.$_REQUEST['act'].'&sizelimit='.$sizelimit.'&sqlcompat='.$sqlcompat.'&sqlcharset='.$sqlcharset.'&tableid='.$tableid.'&fileid='.$fileid.'&startfrom='.$startrow.'&random='.$random.'&submit=1');
			}
			else
			{
				@unlink(PHPMPS_ROOT . 'data/bakup/tables.php');
				show('数据备份成功');
			}
		}
		else
		{
			$query = $db->query("SHOW TABLE STATUS");
			while($table = $db->fetcharray($query)) {
				$table[checked] = $table['Engine'] == 'MyISAM' ? ' ' : 'disabled';
				$totalsize += $table['Data_length'] + $table['Index_length'];
				
				if($table['Data_length']>10240) {
					$table['Data_length']=ceil($table['Data_length']/1024) ."KB";
				}
				$tables[]=$table;
			}
			$totalsize=ceil($totalsize/1024);
		}
	break;

	case 'delete':
		$filename = addslashes_deep($_GET['filename']);
		if(file_exists("../data/bakup/".$filename))@unlink("../data/bakup/".$filename);
		show("备份文件删除成功","database.php?act=importdata");
	break;

	case 'down':
		$filename = addslashes_deep($_GET['filename']);
		ob_clean(); 
		if($fp = @fopen("../data/bakup/".$filename, 'r'))
		{
			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=".$filename);
			header("Accept-Ranges: bytes"); 
			header("Content-Length:".filesize("backup/".$filename)); 
			header('Content-transfer-encoding: binary');
			while (!@feof($fp)) 
			echo fread($fp,10240);
		}
	break;

	case 'importdata':
		
		if($_REQUEST['submit'])
		{
			$filename = addslashes_deep($_GET['filename']);
			if($filename && fileext($filename)=='sql')
			{
				$filepath = PHPMPS_ROOT.'data/bakup/'.$filename;
				if(!file_exists($filepath)) show(" $filepath 不存在");
				$sql = file_get_contents($filepath);
				sql_execute($sql);
				show("$filename 恢复成功");
			}
			else
			{
				$fileid = $_REQUEST['fileid'] ? $_REQUEST['fileid'] : 1;
				$filename = $_REQUEST['pre'].$fileid.'.sql';
				$filepath = PHPMPS_ROOT.'data/bakup/'.$filename;
				
				if(file_exists($filepath))
				{
					$sql = file_get_contents($filepath);
					sql_execute($sql);
					$fileid++;
					show("恢复文件 $filename 成功", "database.php?act=".$_REQUEST['act']."&pre=".$_REQUEST['pre']."&fileid=".$fileid."&submit=1");
				}
				else
				{
					show('数据恢复成功！','database.php');
				}
			}
		}
		else
		{
			$sqlfiles = glob(PHPMPS_ROOT.'data/bakup/*.sql');
			
			if(is_array($sqlfiles))
			{
				$prepre = '';
				$info = $infos = array();
				foreach($sqlfiles as $id=>$sqlfile)
				{
					if(preg_match("/([0-9]{8}_[0-9a-z]{4}_)([0-9]+)\.sql/i",basename($sqlfile),$num))
					{
						$info['filename'] = basename($sqlfile);
						$info['filesize'] = round(filesize($sqlfile)/(1024*1024), 2);
						$info['maketime'] = date('Y-m-d H:i:s', filemtime($sqlfile));
						$info['pre'] = $num[1];
						$info['number'] = $num[2];
						if(!$id) $prebgcolor = '#CFEFFF';
						if($info['pre'] == $prepre)
						{
							$info['bgcolor'] = $prebgcolor;
						}
						else
						{
							$info['bgcolor'] = $prebgcolor == '#CFEFFF' ? '#F1F3F5' : '#CFEFFF';
						}
						$prebgcolor = $info['bgcolor'];
						$prepre = $info['pre'];
						$infos[] = $info;
					}
				}
			}
		}
	break;

	case 'repair':
		if(!empty($_POST))
		{
			$operation = trim($_REQUEST['operation']);
			$tables = is_array($tables) ? implode(',',$tables) : $tables;
			if($tables && in_array($operation,array('repair','optimize')) ) $db->query("$operation TABLE $tables");
			show('操作成功', $PHP_REFERER);
		}
		else
		{
			$tables = array();
			$query = $db->query("SHOW TABLE STATUS");
			while($table = $db->fetcharray($query)) {
				$table['checked'] = $table['Engine'] == 'MyISAM' ? ' ' : 'disabled';
				$totalsize += $table['Data_length'] + $table['Index_length'];
				if($table['Data_length']>10240)
				{
					$table['Data_length']=ceil($table['Data_length']/1024) ."KB";
				}
				$tables[]=$table;
			}
		}
	 break;
}

include tpl('database');

function sql_execute($sql)
{
	global $db;
    $sqls = sql_split($sql);
	if(is_array($sqls))
    {
		foreach($sqls as $sql)
		{
			if(trim($sql) != '') 
			{
				$db->query($sql);
			}
		}
	}
	else
	{
		$db->query($sqls);
	}
	return true;
}

function sql_split($sql)
{
	global $db, $table, $dbcharset;
	if($db->version() > '4.1' && $dbcharset)
	{
		$sql = preg_replace("/TYPE=(InnoDB|MyISAM)( DEFAULT CHARSET=[^; ]+)?/", "TYPE=\\1 DEFAULT CHARSET=".$dbcharset,$sql);
	}
	if($table != "phpmps_") $sql = str_replace("phpmps_", $table, $sql);
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query)
	{
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		$queries = array_filter($queries);
		foreach($queries as $query)
		{
			$str1 = substr($query, 0, 1);
			if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
		}
		$num++;
	}
	return($ret);
}

//生成数据库备份文件
function sql_dumptable($table, $startfrom = 0, $currsize = 0)
{
	global $db, $sizelimit, $startrow, $sqlcompat, $sqlcharset, $dumpcharset;
	if(!isset($tabledump)) $tabledump = '';
	$offset = 100;
	if(!$startfrom)
	{
		$tabledump = "DROP TABLE IF EXISTS $table;\n";
		$createtable = $db->query("SHOW CREATE TABLE $table");
		$create = $db->fetcharray($createtable);
		$tabledump .= $create[1].";\n\n";
		if($sqlcompat == 'MYSQL41' && $db->version() < '4.1')
		{
			$tabledump = preg_replace("/TYPE\=([a-zA-Z0-9]+)/", "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
		}
		if($db->version() > '4.1' && $sqlcharset)
		{
			$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=[a-zA-Z0-9]+/", "DEFAULT CHARSET=".$sqlcharset, $tabledump);
		}
	}
	$tabledumped = 0;
	$numrows = $offset;
	while($currsize + strlen($tabledump) < $sizelimit * 1000 && $numrows == $offset)
	{
		$tabledumped = 1;
		$rows = $db->query("SELECT * FROM $table LIMIT $startfrom, $offset");
		$numfields = $db->num_fields($rows);
		$numrows = $db->num_rows($rows);
		while ($row = $db->fetcharray($rows))
		{
			$comma = "";
			$tabledump .= "INSERT INTO $table VALUES(";
			for($i = 0; $i < $numfields; $i++)
			{
				$tabledump .= $comma."'".mysql_escape_string($row[$i])."'";
				$comma = ",";
			}
			$tabledump .= ");\n";
		}
		$startfrom += $offset;
	}
	$startrow = $startfrom;
	$tabledump .= "\n";
	return $tabledump;
}
?>
