<?php
define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.inc.php';
if(isset($_REQUEST['id']))$catid = intval($_REQUEST['id']);

if($catid) {
	$cat_info = get_cat_info($catid);
	if(empty($cat_info)) {
		header("Location: ./");
		exit;
	}
}

$cat_row = get_cat_children($catid, 'array');
if(!empty($cat_row)) {
	$cat_arr = array();
	foreach($cat_row as $val) {
		$val['catname'] = $val['name'];
		$val['url'] = url_rewrite('category',array('cid'=>$val['id'],'eid'=>$areaid));
		$cat_arr[] = $val;
	}
	$cats = get_cat_children($catid);
}
if(empty($cats))$cats = $catid;

$cat_sql = " and i.catid in ($cats) ";
$page = empty($_REQUEST['page']) ? '1' : intval($_REQUEST['page']);
$sql = "SELECT COUNT(*) FROM {$table}info as i WHERE is_check=1 $cat_sql $area_sql";
$count = $db->getone($sql);
$size = '20';
$pager['search'] = array_merge('catid'=>$catid, 'areaid'=>$areaid);
$pager = get_pager('category.php',$pager['search'],$count,$page,$size);
$sql = "SELECT id,title,postdate,enddate,i.catid,c.catname,i.areaid,a.areaname,thumb,i.description FROM {$table}info AS i LEFT JOIN {$table}category AS c ON i.catid=c.catid LEFT JOIN {$table}area AS a ON a.areaid=i.areaid WHERE is_check=1 $cat_sql $area_sql ORDER BY postdate DESC limit $pager[start],$pager[size]";
$res = $db->query($sql);
$info = array();
while($row=$db->fetchRow($res)) {
	$row['url']      = url_rewrite('view',array('vid'=>$row['id']));
	$row['postdate'] = date('y年m月d日', $row['postdate']);
	$row['lastdate'] = enddate($row['enddate']);
	$row['intro']    = cut_str($row['description'], 50);
	$info[$row['id']] = $row;
}

$seo['title'] = $cat_info['catname'] . '信息列表 - Powered by Phpmps';
include tpl('category');
?>