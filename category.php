<?php

/*
	$s_cat    : 搜索用分类下拉框
	$cats     : 所有子分类编号
	$cat_arr  : 分类导航
	$s_area   : 搜索用分类下拉框
	$areas    : 所有子分类编号
	$area_arr : 分类导航
	$top_type : 置顶类型
	$here_arr : 面包屑导航
	$top_info : 置顶信息
	$articles : 列表分类信息
	$cat_custom : 分类附加属性
*/

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';

$catid = $_REQUEST['id'] ? intval($_REQUEST['id']) : '';
$areaid = $_REQUEST['area'] ? intval($_REQUEST['area']) : '';

if(empty($catid) && empty($areaid)) {
	header("Location: ./");
	exit;
}

if($catid) {
	$cat_info = get_cat_info($catid);
	if(empty($cat_info)) showmsg('不存在此分类', 'index.php');
	$here_arr[] = array('name'=>$cat_info['catname']);
	$cat_parent = $cat_info['parentid'];

	if(empty($cat_parent)) {
		//查看有没有父分类，没有查找有没有子分类
		$cat_row = get_cat_children($catid, 'array');
		//如果有子分类
		if(!empty($cat_row)) {
			//生成搜索
			$s_cat .= '<select name="id" id="id"><option value="0">请选择</option>';
			foreach($cat_row as $cat) {
				$s_cat .= "<option value=$cat[id]>$cat[name]</option>";
			}
			$s_cat .= '</select>';
			//取得所有子分类ID，用逗号连接，用于取得信息
			$cats = get_cat_children($catid);
			if($cats) $cats .= ','.$catid;

			/* 生成导航 */
			$cat_arr = array();
			foreach($cat_row as $val) {
				$val['catname'] = $val['name'];
				$val['url'] = url_rewrite('category', array('cid'=>$val['id'],'eid'=>$areaid));
				$cat_arr[] = $val;
			}
		} else {
			//如果没有，就只有这一级分类，不可选择
			$s_cat .= '<select name="id" id="id" disabled>';
			$s_cat .= "<option value=$catid selected>".$cat_info['catname']."</option>";
			$s_cat .= '</select>';
			$cats = $catid;
		}
		$top_type = '1';//置顶类型为大分类置顶
		
	} else {
		//如果有父分类，取得他所有的子分类
		$cat_row = get_cat_children($cat_parent, 'array');
		/* 生成导航 */
		if(!empty($cat_row))
		{
			foreach($cat_row as $val) {
				$val['catnid'] = $val['id'];
				$val['catname'] = $val['name'];
				$val['url'] = url_rewrite('category',array('cid'=>$val['id'],'eid'=>$areaid));
				$cat_arr[] = $val;
			}
			/* 生成搜索 */
			$s_cat .= '<select name="id" id="id" disabled>';
			foreach($cat_row as $cat) {
				$select = $cat['id'] == $catid ? 'selected' : '';
				$s_cat .= "<option value='$cat[id]' $select>$cat[name]</option>";
			}
			$s_cat .= '</select>';
		}
		$top_type = '2'; //置顶类型为小分类置顶
		$cats = $catid;
	}
	$cat_sql = " and catid in ($cats) ";
	$cat_custom = cat_search_custom($catid);
	$top_info = get_top_info($cats, $top_type);
	if(!empty($top_info)) {
		foreach((array)$top_info as $val) {
			$ids[] = $val['id'];
		}
		$top_info_ids = join(',', $ids);
		$top_info_sql = " and id not in ($top_info_ids)";
	}
} else {
	//不存在分类，就取得所有大分类
	$cat_row = get_parent_cat();
	//生成导航
	if(!empty($cat_row)) {
		foreach($cat_row as $val) {
			$val['url'] = url_rewrite('category', array('cid'=>$val['catid'],'eid'=>$areaid));
			$cat_arr[] = $val;
		}
	}
	//生成搜索
	$s_cat = '<select name="id" id="id"><option value="0">请选择</option>';
	foreach($cat_row as $cat) {
		$s_cat .= "<option value=$cat[catid]>$cat[catname]</option>";
	}
	$s_cat .= '</select>';
}

if($areaid) {
	$area_info = get_area_info($areaid);
	if(empty($area_info)) showmsg('不存在此地区');
	$here_arr[] = array('name'=>$area_info['areaname']);
	$area_parent = $area_info['parentid'];
	if(empty($area_parent)) {
		//大分类
		$area_row = get_area_children($areaid,'array');
		if($area_row) {
			//导航条
			$area_arr = array();
			foreach($area_row as $val) {
				$val['areaname'] = $val['name'];
				$val['url'] = url_rewrite('category',array('cid'=>$catid,'eid'=>$val['id']));
				$area_arr[] = $val;
			}
			//下拉框
			$s_area .= '<select name="area" id="area"><option value="0">请选择</option>';
			foreach($area_row as $cat) {
				$s_area .= "<option value=$cat[id]>$cat[name]</option>";
			}
			$s_area .= '</select>';
		} else {
			$s_area .= '<select name="area" id="area" disabled>';
			$s_area .= "<option value=$areaid selected>".$area_info['areaname']."</option>";
			$s_area .= '</select>';
		}
		$areas = get_area_children($areaid);
		if(!empty($areas)) $areas .= ','.$areaid;
	} else {
		//如果有父分类，取得他所有的子分类
		$area_row = get_area_children($area_parent, 'array');
		/* 生成导航 */
		foreach($area_row as $val) {
			$val['areaid'] = $val['id'];
			$val['areaname'] = $val['name'];
			$val['url'] = url_rewrite('category',array('eid'=>$val['id'],'cid'=>$catid));
			$area_arr[] = $val;
		}
		/* 生成搜索 */
		$s_area .= '<select name="area" id="area">';
		foreach($area_row as $cat) {
			$select = $cat['id'] == $areaid ? 'selected' : '';
			$s_area .= "<option value='$cat[id]' $select>$cat[name]</option>";
		}
		$s_area .= '</select>';
		$areas = $areaid;
	}
	$area_sql = " and areaid in ($areas) ";
} else {
	//没有地区，取所有根地区
	$area_row = get_parent_area();
	if($area_row) {
		$area_arr = array();
		foreach($area_row as $val) {
			$val['areaname'] = $val['areaname'];
			$val['url'] = url_rewrite('category', array('cid'=>$catid, 'eid'=>$val['areaid']));
			$area_arr[] = $val;
		}

		$s_area .= '<select name="area" id="area"><option value="0">请选择</option>';
		foreach($area_row as $area) {
			$s_area .= "<option value=$area[areaid]>$area[areaname]</option>";
		}
		$s_area .= '</select>';
	}
}

$area_array = get_area_array();
$cat_array = get_cat_array();
$page = empty($_REQUEST['page']) ? '1' : intval($_REQUEST['page']);
$sql = "SELECT COUNT(*) FROM {$table}info as i WHERE is_check=1 $cat_sql $area_sql";
$count = $db->getOne($sql);
$size = '10';
$pager = page('category', $catid, $areaid, $count, $size, $page);
$sql = "SELECT id,title,postdate,enddate,catid,areaid,thumb,description FROM {$table}info WHERE is_check=1 $cat_sql $area_sql $top_info_sql ORDER BY postdate DESC limit $pager[start], $pager[size]";
$res = $db->query($sql);
$info = array();
while($row=$db->fetchRow($res)) {
	$row['url']      = url_rewrite('view',array('vid'=>$row['id']));
	$row['postdate'] = date('y年m月d日', $row['postdate']);
	$row['lastdate'] = enddate($row['enddate']);
	$row['intro']    = cut_str($row['description'], 50);
	$row['areaname'] = $area_array[$row['areaid']];
	$row['catname']  = $cat_array[$row['catid']];
	$info[$row['id']] = $row;
}

if($info) {
	foreach($info as $val) {
		$infoid .= $val['id'].',';
	}
	$infoid = substr($infoid,0,-1);
	$info_custom = get_infos_custom($infoid);
	foreach($info as $key=>$val) {
		$info[$key]['custom'] = is_array($info_custom[$key]) ? $info_custom[$key] : array();
	}
}

$cat_pro = get_info($cats, $areas, '8', 'pro','','10'); //推荐信息
$cat_hot = get_info($cats, $areas, '8', '','click','10'); //热门信息

$here = get_here($here_arr);
$seo['title'] = $area_info['areaname'] . $cat_info['catname'] . '信息列表 - Powered by Phpmps';
$seo['keywords'] = $area_info['areaname'].$cat_info['keywords'];
$seo['description'] = $cat_info['description'];

$template = $cat_info['cattplname'] ? $cat_info['cattplname'] : 'category';
include template($template);
?>