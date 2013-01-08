<?php
if(!defined('IN_PHPMPS'))
{
	die('Access Denied');
}

function get_com_parent_cat()
{
	global $db,$table;
	
	$data = read_cache('com_parent_cat');
	if ($data === false) {
		$res = $db->query("select catid,catname from {$table}com_cat where parentid = '0' ");
		while($row=$db->fetchrow($res)) {
			$parent_cat[] = $row;
		}
		write_cache('com_parent_cat', $parent_cat);
	} else {
		$parent_cat = $data;
	}
	return $parent_cat;
}

function get_com_cat_list()
{
	global $db,$table;
	
	static $cats = NULL;
	if ($cats === NULL) {
		$data = read_cache('com_cat_list');
		if ($data === false) {
			$res = $db->getAll("select a.catid, a.catname, a.catorder as catorder ,b.catid as childid, b.catname as childname, b.catorder as chiorder from {$table}com_cat as a left join {$table}com_cat as b on b.parentid = a.catid where a.parentid = '0' order by catorder,a.catid,chiorder asC");

			$cats = array();
			foreach ($res as $row) {
				$cats[$row['catid']]['catid']   = $row['catid'];
				$cats[$row['catid']]['catname'] = $row['catname'];
				$cats[$row['catid']]['caturl']  = url_rewrite('com',array('act'=>'list','catid'=>$row['catid']));

				if(!empty($row['childid'])) {
					$cats[$row['catid']]['children'][$row['childid']]['id']   = $row['childid'];
					$cats[$row['catid']]['children'][$row['childid']]['name'] = $row['childname'];
					$cats[$row['catid']]['children'][$row['childid']]['url']  = 
					url_rewrite('com', array('act'=>'list','catid'=>$row['childid']));
				}
			}
			write_cache('com_cat_list', $cats);
		} else {
			$cats = $data;
		}
	}
	return $cats;
}

function com_cat_options($selectid='',$catid='') 
{
	$cats = get_com_cat_list();
	if($catd){$cats = $cats[$catid];}
	foreach((array)$cats as $cat) {
		$option .= "<option value=$cat[catid] style='color:red;'";
		$option .= ($selectid == $cat['catid']) ? " selected='selected'" : '';
		$option .= ">$cat[catname]</option>";

		if(!empty($cat['children'])) {
			foreach($cat['children'] as $chi)
			{
				$option .= "<option value=$chi[id]";
				$option .= ($selectid == $chi['id']) ? " selected='selected'" : '';
				$option .= ">&nbsp;&nbsp;|--$chi[name]</option>";
			}
		}
	}
	return $option;
}

function get_com_cat_children($catid,$type='int')
{
	$cats = get_com_cat_list();
	$cat_children = $cats[$catid]['children'];

	if(is_array($cat_children)) {
		if($type=='int') {
			foreach($cat_children as $child) {
				$id .= $child['id'].',';
			}
			$result = substr($id,0,-1);
		} elseif($type=='array') {
			$result = $cat_children;
		}
	} else {
		if($type=='int') {
			$result = $catid;
		} elseif($type=='array') {
			$result = '';
		}
	}
	return $result;
}

function get_com_cat_info($catid)
{
	global $db,$table;
	
	$data = read_cache('com_cat_'.$catid);
	if ($data === false) {
		$cat_info = $db->getRow("select * from {$table}com_cat where catid='$catid' ");
		write_cache('com_cat_'.$catid, $cat_info);
	} else {
		$cat_info = $data;
	}
	return $cat_info;
}

?>