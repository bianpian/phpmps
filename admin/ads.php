<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('ads');

//初始化act操作
$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

$type = array('1'=>'文字广告','图片广告','flash广告','代码广告');

$sql = "SELECT * FROM {$table}ads_place";
$ads_place = $db->getAll($sql);

switch ($_REQUEST['act'])
{
	case 'list':
		$res = $db->query("SELECT a.*,p.placename FROM {$table}ads AS a LEFT JOIN {$table}ads_place AS p ON p.placeid = a.placeid ");
		$ads = array();
		while($row = $db->fetchRow($res)) {
			$row['adstype']  = $type[$row['adstype']];
			$row['addtime']  = date('Y-m-d',$row['addtime']);
			$ads[] = $row;
		}
		//$here = "广告列表";
		$action = array('name'=>'添加广告', 'href'=>'ads.php?act=add');
	    include tpl('list_ads');
	break;

	case 'add':
		$here = "添加广告";
		$action = array('name'=>'广告列表', 'href'=>'ads.php?act=list');
		include tpl('add_ads');
	break;

	case 'insert':
		if(empty($_POST['placeid']))show('请选择广告位');
		if(empty($_POST['adstype']))show('请选择广告类型');
		if(empty($_POST['adsname']))show('请填写广告名称');

		$placeid = intval($_POST['placeid']);
		$adstype = intval($_POST['adstype']);
		$adsname = trim($_POST['adsname']);

		if($adstype != '3' || $adstype!='4')$adsurl = !empty($_POST['adsurl']) ? trim($_POST['adsurl']) : '';

		/* 查看广告名称是否有重复 */
		$sql = "SELECT COUNT(*) FROM {$table}ads WHERE adsname = '$adsname'";
		if( $db->getOne($sql))show('以存在此广告名称');

		if($adstype == '1')//广告类型为文本广告
		{
			if (!empty($_POST['adscontent']))
			{
				$adscode = trim($_POST['adscontent']);
			}
			else
			{
				show('文本为空');
			}
		}
		elseif($adstype == '2')/* 添加图片类型的广告 */
		{
			if((isset($_FILES['image']['error']) && $_FILES['image']['error'] == 0) || (!isset($_FILES['image']['error']) && isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name'] != 'none'))
			{
				$name = date('ymdhis');
				for($i = 0;$i < 6;$i++) 
				{
					$name .= chr(mt_rand(97, 122));
				}
				$name .= '.' . end(explode('.', $_FILES['image']['name']));
				$to = PHPMPS_ROOT . 'data/ads/' . $name;
				if(move_uploaded_file($_FILES['image']['tmp_name'], $to))
				{
					$adscode = "data/ads/" . $name;
				}
				else
				{
					show('图片上传失败');
				}
			}
			if(!empty($_POST['imageurl']))
			{
				$adscode = $_POST['imageurl'];
			}
			if((isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name'] == 'none') && empty($_POST['imageurl']))
			{
				show('图片或图片地址为空');
			}
		}
		elseif ($adstype == '3')/* 如果添加的广告是Flash广告 */
		{
			if ((isset($_FILES['flash']['error']) && $_FILES['flash']['error'] == 0) || (!isset($_FILES['flash']['error']) && isset($_FILES['image']['tmp_name']) && $_FILES['flash']['tmp_name'] != 'none'))
			{
				/* 检查文件类型 */
				if ($_FILES['flash']['type'] != "application/x-shockwave-flash")
				{
					show('上传flash类型错误');
				}

				/* 生成文件名 */
				$urlstr = date('ymdhis');
				for($i = 0; $i < 6; $i++)
				{
					$urlstr .= chr(mt_rand(97, 122));
				}

				$source_file = $_FILES['flash']['tmp_name'];
				$target = PHPMPS_ROOT . 'data/ads/';
				$file_name = $urlstr .'.swf';

				if(move_uploaded_file($source_file, $target.$file_name))
				{
					$adscode = "data/ads/" . $file_name;
				}
				else
				{
					show('上传flash失败');
				}
			}
			elseif(!empty($_POST['flashurl']))
			{
				if (substr(strtolower($_POST['flashurl']), strlen($_POST['flashurl']) - 4) != '.swf')
				{
					show('连接flash类型错误');
				}
				$adscode = trim($_POST['flashurl']);
			}

			if (((isset($_FILES['flash']['error']) && $_FILES['flash']['error'] > 0) || (!isset($_FILES['flash']['error']) && isset($_FILES['flash']['tmp_name']) && $_FILES['flash']['tmp_name'] == 'none')) && empty($_POST['flashurl']))
			{
				show('上传flash或flash地址为空');
			}
		}
		elseif ($adstype== '4')/* 如果广告类型为代码广告 */
		{
			if (!empty($_POST['adscode']))
			{
				$adscode = $_POST['adscode'];
			}
			else
			{
				show('代码为空');
			}
		}
		
		$addtime = time();
		/* 插入数据 */
		$sql = "INSERT INTO {$table}ads (placeid,adstype,adsname,adsurl,adscode,addtime,linkman)
		VALUES ('$placeid','$adstype','$adsname','$adsurl','$adscode','$addtime','$_POST[postman]')";
		$db->query($sql);

		admin_log("添加广告 $adsname 成功");//添加操作记录
		$link = 'ads.php?act=add';
		show('添加广告成功', $link);
	break;
	
	case 'edit':
		$id = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}ads WHERE adsid = '$id'"; 
		$ads = $db->getRow($sql);
		include tpl('edit_ads');
	break;

	case 'update':
		if(empty($_POST['placeid']))show('请选择广告位');
		if(empty($_POST['adsname']))show('请填写广告名称');
		
		$adsid   = intval($_REQUEST['adsid']);
		$placeid = intval($_REQUEST['placeid']);
		$adstype = intval($_REQUEST['adstype']);
		$adsname = trim($_REQUEST['adsname']);

		if($adstype != '4')$adsurl = !empty($_POST['adsurl']) ? trim($_POST['adsurl']) : '';
		
		$sql = "select adscode from {$table}ads where adsid='$adsid' ";
		$adscode = $db->getOne($sql);

		if($adstype == '1')//广告类型为文本广告
		{
			if (!empty($_POST['adscontent']))
			{
				$adscode = trim($_POST['adscontent']);
			}
			else
			{
				show('文本为空');
			}
		}
		elseif($adstype == '2')/* 添加图片类型的广告 */
		{
			if((isset($_FILES['image']['error']) && $_FILES['image']['error'] == 0) || (!isset($_FILES['image']['error']) && isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name'] != 'none'))
			{
				$name = date('ymdhis');
				for($i = 0;$i < 6;$i++) 
				{
					$name .= chr(mt_rand(97, 122));
				}
				$name .= '.' . end(explode('.', $_FILES['image']['name']));
				$to = PHPMPS_ROOT . 'data/ads/' . $name;
				if(move_uploaded_file($_FILES['image']['tmp_name'], $to))
				{
					if((strpos($adscode, 'http://') === false) && (strpos($adscode, 'https://') === false))
					{
						$img_name = basename($img);
						@unlink(PHPMPS_ROOT.'data/ads/'.$img_name);
					}
					$adscode = "data/ads/" . $name;
				}
				else
				{
					show('图片上传失败');
				}
			}
			if(!empty($_POST['imageurl']))
			{
				$adscode = $_POST['imageurl'];
			}
			if((isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name'] == 'none') && empty($_POST['imageurl']))
			{
				show('图片或图片地址为空');
			}
		}
		elseif ($adstype == '3')/* 如果添加的广告是Flash广告 */
		{
			if ((isset($_FILES['flash']['error']) && $_FILES['flash']['error'] == 0) || (!isset($_FILES['flash']['error']) && isset($_FILES['image']['tmp_name']) && $_FILES['flash']['tmp_name'] != 'none'))
			{
				/* 检查文件类型 */
				if ($_FILES['flash']['type'] != "application/x-shockwave-flash")
				{
					show('上传flash类型错误');
				}

				/* 生成文件名 */
				$urlstr = date('ymdhis');
				for($i = 0; $i < 6; $i++)
				{
					$urlstr .= chr(mt_rand(97, 122));
				}

				$source_file = $_FILES['flash']['tmp_name'];
				$target = PHPMPS_ROOT . 'data/ads/';
				$file_name = $urlstr .'.swf';

				if(move_uploaded_file($source_file, $target.$file_name))
				{
					if((strpos($adscode, 'http://') === false) && (strpos($adscode, 'https://') === false))
					{
						$img_name = basename($img);
						@unlink(PHPMPS_ROOT.'data/ads/'.$img_name);
					}
					$adscode = $file_name;
				}
				else
				{
					show('上传flash失败');
				}
			}
			elseif(!empty($_POST['flashurl']))
			{
				if (substr(strtolower($_POST['flashurl']), strlen($_POST['flashurl']) - 4) != '.swf')
				{
					show('连接flash类型错误');
				}
				$adscode = trim($_POST['flashurl']);
			}

			if (((isset($_FILES['flash']['error']) && $_FILES['flash']['error'] > 0) || (!isset($_FILES['flash']['error']) && isset($_FILES['flash']['tmp_name']) && $_FILES['flash']['tmp_name'] == 'none')) && empty($_POST['flashurl']))
			{
				show('上传flash或flash地址为空');
			}
		}
		elseif ($adstype== '4')/* 如果广告类型为代码广告 */
		{
			if (!empty($_POST['adscode']))
			{
				$adscode = $_POST['adscode'];
			}
			else
			{
				show('代码为空');
			}
		}
		$updatetime = time();
		
		$sql = "UPDATE {$table}ads SET placeid = '$placeid', adsname = '$adsname', adsurl = '$adsurl',adscode='$adscode',updatetime='$updatetime',linkman='$_POST[linkman]' WHERE adsid = '$adsid'";
		$db->query($sql);
		
		admin_log("修改广告 $adsname 成功");
		$link = 'ads.php?act=list';
		show('修改广告成功', $link);
	break;

	case 'batch':
		$id = is_array($_REQUEST['id']) ? join(',',$_REQUEST['id']) : intval($_REQUEST['id']);
		if(empty($id))show('没有选择记录');
		$sql = "SELECT * FROM {$table}ads WHERE adsid in ($id)";
		$ads = $db->getAll($sql);
		
		foreach((array)$ads as $ad) {
			if($ads[adstype]==2 && $ads['adscode']!='' && is_file(PHPMPS_ROOT.'/data/ads/'.$ads['adscode'])) {
				@unlink('../'.$ads['adscode']);
			}
		}
		$sql = "DELETE FROM {$table}ads WHERE adsid in ($id)";
	    $res = $db->query($sql);

		admin_log("删除广告 $id 成功");
		$link = 'ads.php?act=list';
		show('删除广告成功', $link);
	break;
}
?>