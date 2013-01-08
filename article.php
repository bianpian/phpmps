<?php

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';

$act = $_REQUEST['act'] ? $_REQUEST['act'] : 'list';

if($act == 'list')
{
	$res = $db->query("select * from {$table}type where module='article'");
	$type = array();
	while($row = $db->fetchrow($res)) {
		$row['url'] = url_rewrite('article', array('iid'=>$row['typeid'],'act'=>'list'));
		$type[] = $row;
	}
	$typeid  = !empty($_REQUEST['typeid']) ? intval($_REQUEST['typeid']) : '';
	$typewhere = $typeid ? " AND typeid = '$typeid' " : '';

	$page = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
	$size = !empty($_CFG['pagesize']) && intval($_CFG['pagesize']) > 0 ? intval($_CFG['page_size']) : 20;

	$count = $db->getOne("SELECT COUNT(*) FROM {$table}article WHERE 1 ".$typewhere);
	$max_page = ($count> 0) ? ceil($count / $size) : 1;
	if($page>$max_page)$page = $max_page;
	$pager['search'] = array('typeid' => $typeid);
	$pager = page('article', $typeid, '', $count, $size, $page);

	$sql = "SELECT * FROM {$table}article WHERE 1 " .$typewhere. " ORDER BY listorder desc,id DESC LIMIT $pager[start],$pager[size]";
	$res = $db->query($sql);
	$articles = array();
	while($row = $db->fetchRow($res)) {
		$row['title']    = cut_str($row['title'],'18');
		$row['addtime']  = date('Y-m-d h:i', $row['addtime']);
		$row['url']      = url_rewrite('article',array('aid'=>$row['id'],'act'=>'view'));
		$articles[]      = $row;
	}
	$pro_articles = get_article_pro('',10,20);

	/* 所处位置 */
	$cat_info = $db->getRow("SELECT * FROM {$table}type WHERE typeid = '$typeid'");
	if(empty($cat_info)) {
		$here_arr[] = array('name'=>'全部资讯');
	} else {
		$here_arr[] = array('name'=>$cat_info['typename']);
	}
	$here = get_here($here_arr);
	$seo['title'] = $cat_info['typename'] . '资讯列表';
	$seo['keywords'] = $cat_info['keywords'];
	$seo['description'] = $cat_info['description'];

	include template('article_list');
}
elseif($act=='view')
{
	$id = intval($_REQUEST['id']);
	if(empty($id))  showmsg('缺少参数！');
	$article = $db->getRow("SELECT * FROM {$table}article WHERE id='$id'");
	if(empty($article))showmsg('不存在此文章', 'index.php');
	extract($article);
	$addtime = date('Y-m-d H:i', $addtime);

	/* 取得关连文章 */
	$match_article = array();
	$res = $db->query("SELECT id,title FROM {$table}article WHERE typeid='$typeid' ORDER BY id DESC LIMIT 0,5 ");
	while($row = $db->fetchrow($res)) {
		if($row['id'] == $id) continue;
		$row['url'] = url_rewrite('article',array('aid'=>$row['id'],'act'=>'view'));
		$match_article[] = $row;
	}
	$pro_articles = get_article_pro('',10,20);

	/* 上一条，下一条 */
	$sql = "select id,title from {$table}article where id>$id and typeid=$typeid ";
	$next = $db->getRow($sql);
	if(!empty($next))$next[url] = url_rewrite('article', array('act'=>'view','aid'=>$next['id']));
	$pid = $db->getOne("select max(id) from {$table}article where id<$id and typeid=$typeid limit 1");
	if(!empty($pid)) {
		$sql = "select id,title from {$table}article where id = '$pid' ";
		$pre = $db->getRow($sql);
		if(!empty($pre))$pre[url] = url_rewrite('article', array('aid'=>$pre['id'],'act'=>'view'));
	}

	$row = $db->getRow("SELECT * FROM {$table}type WHERE typeid = '$typeid'");
	$here_arr[] = array('name'=>$row['typename'],'url'=>url_rewrite('article',array('iid'=>$row['typeid'],'act'=>'list')));
	$here_arr[] = array('name'=>$title);
	$here = get_here($here_arr);

	$seo['title']   = $title;
	$seo['keywords']  = !empty($keywords) ? $keywords : cut_str($title,'15');
	$seo['description'] = $description;

	include template('article');
}

function get_article_pro($typeid='', $num='10', $len='20', $thumb='')
{
	global $db,$table;

	if(!empty($typeid)) {
		$where .= " AND typeid in ($typeid)";
	}
	$sql = "select * from {$table}article where is_pro=1 $where order by listorder,id desc limit 0,$num";
	$res = $db->query($sql);
	$data = array();
	while($row = $db->fetchRow($res)) {
		$row['title']    = cut_str($row['title'],$len);
		$row['addtime']  = date('Y-m-d h:i', $row['addtime']);
		$row['url']      = url_rewrite('article', array('aid'=>$row['id'],'act'=>'view'));
		$data[]  = $row;
	}
	return $data;
}
?>