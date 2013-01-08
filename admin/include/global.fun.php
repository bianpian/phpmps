<?php

if (!defined('IN_PHPMPS'))
{
    die('Access Denied');
}

/*
 * 查看管理员信息
 * @param int id 管理员编号
 */
function chkadmin($purview)
{
	global $db,$table;
	$sql = "SELECT is_admin,purview FROM {$table}admin WHERE userid='$_SESSION[adminid]'";
	$row = $db->getRow($sql);
	if(!$row['is_admin']){
		$purviews = explode(",", $row['purview']);
		if(!in_array("$purview", $purviews)) {
			show('您没有操作权限');
		}
	}
}

/* 
 * 添加管理日志 
 * @param   string  logtype  操作名称
 */
function admin_log($logtype)
{
	global $db,$table;
    $sql = "INSERT INTO {$table}admin_log (adminname,logdate,logtype,logip) VALUES ('$_SESSION[adminname]','".time()."','$logtype','$_SERVER[REMOTE_ADDR]')";
    $db->query($sql);
}

/**
 * 生成编辑器
 * @param   string  $editor_name  编辑器名称
 * @param   string  $type         编辑器类型
 * @param   string  $value        编辑器赋值
 */
function fck_editor($editor_name,$type,$value = '',$width='90%',$height='320')
{
    $editor = new FCKeditor($editor_name);
    $editor->BasePath   = '../include/fckeditor/';
    $editor->ToolbarSet = $type;
    $editor->Width      = $width;
    $editor->Height     = $height;
    $editor->Value      = $value;
    $FCKeditor = $editor->CreateHtml();
	return $FCKeditor;
}

/**
 * 引用模板
 * @param   string  file  模板名称
 */
function tpl($file)
{
	global $CFG;
	$file = PHPMPS_ROOT.'admin/templates/'.$file.'.htm';
    return $file;
}

/**
 * 提示信息
 * @param   string  msg  提示信息
 */
function show($msg,$url='goback')
{
    include tpl('show');
	exit;
}

/**
 * 取得帮助,新闻等的分类
 * @param string type 类型名称
 */
function type_select($type='help',$typeid='')
{
	global $db,$table;
	
	$res = $db->query("select * from {$table}type where module='$type'");
	$option = "<select name=\"typeid\" id=\"typeid\">";
	while($row=$db->fetchrow($res)) {
		$option .= "<option value=$row[typeid]";
		$option .= ($typeid == $row[typeid]) ? " selected='selected'" : '';
		$option .= ">$row[typename]</option>";
	}
	$option .= "</select>";
	return $option;
}

/**
 * 获取某个字段的最大值
 * @param string field 字段名称
 * @param string tables 表名
 */
function getFieldMax($field, $tables)
{
	global $db,$table;
	$data = $db->getOne("SELECT MAX(".$field.") FROM {$table}{$tables}");
	return $data;
}

?>