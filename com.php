<?php

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';
require dirname(__FILE__) . '/include/com.fun.php';
$act = $_REQUEST['act'] ? $_REQUEST['act'] : 'list' ;

if($act=='list')
{
	$catid = $_REQUEST['catid'] ? intval($_REQUEST['catid']) : '';
	$areaid = $_REQUEST['area'] ? intval($_REQUEST['area']) : '';
	$page = $_REQUEST['page'] ? intval($_REQUEST['page']) : '1';

	$com_cats = get_com_cat_list();/* 企业分类列表 */
	if($catid) {
		$com_cat_info = get_com_cat_info($catid);

		if(empty($com_cat_info['parentid'])) {
			$cats = get_com_cat_children($catid);
			if(empty($cats))$cats=$catid;
		} else {
			$cats = $catid;
		}
		$cat_sql = " and catid in ($cats) ";
	}
	if(!$areaid) {
		$area_row = get_parent_area();
		if(!empty($area_row)) {
			$area_arr = array();
			foreach($area_row as $val) {
				$val['areaname'] = $val['areaname'];
				$val['url'] = url_rewrite('com', array('act'=>'list', 'catid'=>$catid,'eid'=>$val['areaid']));
				$area_arr[] = $val;
			}
		}
	} else {
		$area_info = get_area_info($areaid);
		$area_parent = $area_info['parentid'];

		if(empty($area_parent)) {
			$area_row = get_area_children($areaid,'array');
			if(!empty($area_row)) {
				$area_arr = array();
				foreach($area_row as $val) {
					$val['areaname'] = $val['name'];
					$val['url'] = url_rewrite('com',array('act'=>'list', 'catid'=>$catid,'eid'=>$val['id']));
					$area_arr[] = $val;
				}
				$areas = get_cat_children($areaid);
			}
			if(empty($areas))$areas = $areaid;
		} else {
			$areas = $areaid;
		}
		$area_sql = " and areaid in ($areas) ";
	}
	$area_array = get_area_array();
	$cat_array = get_cat_array();
	$sql = "SELECT COUNT(*) FROM {$table}com as i WHERE is_check=1 $cat_sql $area_sql";
	$count = $db->getOne($sql);
	$size = $CFG['com_pagesize'] ? $CFG['com_pagesize'] : 18;
	$pager = page('com',$catid,$areaid,$count,$size,$page);
	$sql = "SELECT * FROM {$table}com WHERE is_check=1 $cat_sql $area_sql ORDER BY postdate DESC limit $pager[start],$pager[size]";
	$res = $db->query($sql);
	$articles = array();
	while($row=$db->fetchRow($res)) {
		$row['sname']      = cut_str($row['comname'],10);
		$row['postdate']   = date('y年m月d日', $row['postdate']);
		$row['thumb']      = PHPMPS_PATH.$row['thumb'];
		$row['introduce']  = cut_str($row['introduce'],30);
		$row['areaname']   = $area_array[$row['areaid']];
		$row['catname']    = $cat_array[$row['catid']];
		$row['url']        = url_rewrite('com',array('act'=>'view','comid'=>$row['comid']));
		$articles[] = $row;
	}

	if(empty($com_cat_info) && empty($area_info)) {
		$here_arr[] = array('name'=> '黄页列表');
	} elseif(empty($com_cat_info) && !empty($area_info)) {
		$here_arr[] = array('name'=> '黄页列表','url'=>url_rewrite('com', array('act'=>'list', 'catid'=>$catid)));
	} else {
		$here_arr[] = array('name'=>$com_cat_info['catname'],'url'=>url_rewrite('com', array('act'=>'list', 'catid'=>$catid)));
	}

	$here_arr[] = array('name'=>$area_info['areaname'],'url'=>url_rewrite('com', array('act'=>'list', 'eid'=>$areaid)));
	$here = get_here($here_arr);

	$seo['title'] = $area_info['areaname'] . $com_cat_info['catname'] . '黄页列表';
	$seo['keywords'] = $area_info['areaname'].$com_cat_info['keywords'];
	$seo['description'] = $com_cat_info['description'];

	include template('com_list');
}
elseif($act=='view')
{
	$comid = intval($_REQUEST['id']);
	if(empty($comid)) showmsg('缺少参数！');
	$com_info = $db->getRow("select * from {$table}com where comid='$comid' ");
	if(empty($com_info)) showmsg('信息不存在','index.php');
	unset($com_info['userid']);
	extract($com_info);
	if(!$is_check)showmsg('黄页尚未审核，审核后可浏览！');
	$mappoint = $mappoint ? explode(',', $mappoint) : '';
	$thumb = PHPMPS_PATH.$thumb;
	
	$res = $db->query("select * from {$table}com_image where comid='$comid' ");
	$com_images = array();
	while($row=$db->fetchRow($res)) {
		$row['path'] = PHPMPS_PATH . $row['path'];
		$com_images[] = $row;
	}
	$db->query("UPDATE {$table}com SET click=click+1 WHERE comid='$comid'");
	
	$res = $db->query("select comid,comname,thumb,postdate from {$table}com order by comid desc,click desc limit 10");
	$match_com = array();
	while($row=$db->fetchrow($res)) {
		$row['sname'] = cut_str($row['comname'],12);
		$row['postdate'] = date('y-m-d', $row['postdate']);
		$row['url'] = url_rewrite('com', array('act'=>'view', 'comid'=>$row['comid']));
		$match_com[] = $row;
	}
	
	$cat_info = get_com_cat_info($catid);
	$here_arr[] = array('name'=>$cat_info['catname'],'url'=>url_rewrite('com',array('act'=>'list','catid'=>$catid)));
	$here_arr[] = array('name'=>$comname);
	$here = get_here($here_arr);

	$seo['title']   = $comname;
	$seo['keywords']  = !empty($keywords) ? $keywords : cut_str($title,'5');
	$seo['description'] = !empty($description) ? $description : cut_str(strip_tags($content),50);

	include template('com_view');
}
?>