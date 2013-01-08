<?php
define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';

$id = $_REQUEST['id'] ? intval($_REQUEST['id']) : '';
if(empty($id)) showmsg('缺少参数！');
$sql = "SELECT a.*,m.username FROM {$table}info AS a LEFT JOIN {$table}member AS m ON m.userid=a.userid WHERE a.id='$id'";
$info = $db->getRow($sql);
if(empty($info)) showmsg('信息不存在','index.php');
$info['content'] = strip_tags($info['content']);
$info['infouserid'] = $info['userid'];
unset($info['userid']);
extract($info);
$content = str_replace("\n","<br />", htmlspecialchars($content));
$cat_array = get_cat_array();
$area_array = get_area_array();
$catname = $cat_array[$catid];
$areaname = $area_array[$areaid];

$phone_c = $phone;
$email_c = $email;
$qq_c    = $qq;

if($email)$crypt_email = encrypt($email,$CFG['crypt']);
if($qq)$js_qq = encrypt($qq, $CFG['crypt']);

$link_image = 1;
if($link_image == '1') {
	$phone = empty($phone) ? '' : '<img src="do.php?act=show&num='.encrypt($phone, $CFG['crypt']).'" align="absbottom">';
	$email = empty($email)? '' : '<img src="do.php?act=show&num='.encrypt($email,	$CFG['crypt']).'" align="absbottom">';
	$qq = empty($qq)? '' : '<img src="do.php?act=show&num='.encrypt($qq, $CFG['crypt']).'" align="absbottom">';
} else {
	$phone = $phone_c;
	$email = $email_c;
	$qq = $qq_c;
}
if(!$CFG['visitor_view']) {
	if(empty($_userid)) {
		$phone=empty($phone) ? '' : '登陆后显示';
		$email=empty($email) ? '' : '登陆后显示';
		$qq=empty($qq) ? '' : '登陆后显示';
	}
}
if(!$CFG['expired_view']) {
	if($enddate<time() && $enddate>0) {
		$phone=empty($phone) ? '' : '已过期';
		$email=empty($email) ? '' : '已过期';
		$qq=empty($qq) ? '' : '已过期';
	}
}
$postdate = date('Y年m月d日', $postdate);
$lastdate = enddate($enddate);
$mappoint = $mappoint ? explode(',', $mappoint) : '';
if(!$is_check)showmsg('信息尚未审核，审核后可浏览！', 'index.php');
$custom = get_info_custom($id);
$images = $db->getAll("SELECT * FROM {$table}info_image WHERE infoid = '$id' ");
$db->query("UPDATE LOW_PRIORITY {$table}info SET click=click+1 WHERE id='$id'");/*更新点击量*/

/*取得关联信息*/
$match_info = array();
$res = $db->query("SELECT id,title FROM {$table}info WHERE is_check=1 AND catid='$catid' ORDER BY id DESC LIMIT 0,5 ");
while($row = $db->fetchrow($res)) {
	if($row['id'] != $id) continue;
	$row['url'] = url_rewrite('view', array('vid'=>$row['id']));
	$match_info[] = $row;
}

$here_arr[] = array('name'=>$catname, 'url'=>url_rewrite('category',array('cid'=>$catid)));
$here_arr[] = array('name'=>$title);
$here = get_here($here_arr);

$seo['title']   = $title . ' - Powered by Phpmps';
$seo['keywords']  = !empty($keywords) ? $keywords : cut_str($title,'5');
$seo['description'] = !empty($description) ? $description : cut_str(strip_tags($content),50);

$cat_info = get_cat_info($catid);
$template = $cat_info['viewtplname'] ? $cat_info['viewtplname'] : 'view';
include template($template);
?>