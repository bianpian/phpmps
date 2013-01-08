<?php

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';
require dirname(__FILE__) . '/include/com.fun.php';
require PHPMPS_ROOT . 'include/json.class.php';

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'select' ;

if(!$_userid) {
	showmsg('你还没有登录', 'member.php?act=login&refer='.$PHP_URL);
} else {
	$member = member_info($_userid);
	if($member['status']!=1) showmsg('您尚未通过审核或被锁定！');
}
$ip = get_ip();
$postarea = getPostArea($ip);
onlyarea($postarea);

if($_REQUEST['act'] == 'select')
{
	$seo['title'] = '选择企业分类';
	$seo['keywords'] = $CFG['keywords'];
	$seo['description'] = $CFG['description'];

	$here = array('name'=>'选择企业分类','url'=>'postcom.php');
	$cats = get_com_cat_list();
	include template('com_select','com');
}
elseif($_REQUEST['act'] == 'post')
{
	$mappoint = $CFG['map'] ? explode(',', $CFG['map']) : '';
	$catid = intval($_REQUEST['id']);
	if(empty($catid)) {
		showmsg('没有选择分类');
	}
	$com_catinfo = get_com_cat_info($catid);
	if(empty($com_catinfo))showmsg('不存在此企业分类');

	$seo['title'] = '发布企业信息';
	$seo['keywords'] = $CFG['keywords'];
	$seo['description'] = $CFG['description'];

	include template('com_post','com');
}
elseif($_REQUEST['act'] == 'postok')
{
	$catid     = $_POST['catid'] ? intval($_POST['catid']) : '';
	$comname   = $_POST['comname'] ? htmlspecialchars(trim($_POST['comname'])) : '';
	$areaid    = $_POST['areaid'] ? intval($_POST['areaid']) : '';
	$postdate  = time();
	$introduce = $_POST['content'] ? htmlspecialchars(trim($_POST['content'])) : '';;
	$hours     = $_POST['hours'] ? htmlspecialchars(trim($_POST['hours'])) : '';
	$keywords  = $_POST['keyword'] ? htmlspecialchars(trim($_POST['keyword'])) : '';
	$description = cut_str($introduce,30);
	$linkman   = $_POST['linkman'] ? htmlspecialchars(trim($_POST['linkman'])) : '';
	$phone     = $_POST['phone'] ? trim($_POST['phone']) : '';
	$qq        = $_POST['qq'] ? intval($_POST['qq']) : '';
	$email     = $_POST['email'] ? htmlspecialchars(trim($_POST['email'])) : '';
	$fax       = $_POST['address'] ? trim($_POST['fax']) : '';
	$address   = $_POST['address'] ? trim($_POST['address']) : '';
	$mappoint  = $_POST['mappoint'] ? trim($_POST['mappoint']) : '';
	$is_check  = $CFG['post_check'] == '1' ?  '0' : '1';
	
    if(empty($comname))showmsg("标题不能为空");
    if(empty($phone) && empty($qq) && empty($email))showmsg("联系方式必须填写一项");
	check_words(array($comname, $introduce));
	
    $sql = "insert into {$table}com (userid,catid,areaid,comname,keywords,description,introduce,linkman,email,fax,qq,phone,postdate,mappoint,address,hours,is_check) 
	values ('$_userid','$catid','$areaid','$comname','$keywords','$description','$introduce','$linkman','$email','$fax','$qq','$phone','$postdate','$mappoint','$address','$hours',$is_check)";
    $res = $db->query($sql);
	$id = $db->insert_id();
	
	$alled = array('png','jpg','gif','jpeg');
	$exname = strtolower(trim(substr(strrchr($_FILES['thumb']['name'], '.'), 1)));
	if(checkupfile($_FILES['thumb']['tmp_name']) && $_FILES['thumb']['tmp_name'] != 'none' && $_FILES['thumb']['tmp_name'] && $_FILES['thumb']['name'] && $_FILES['thumb']['size']<'523298' && in_array($exname,$alled)) {

		$thumb_name = $id.'_thumb'. '.' . end(explode('.', $_FILES['thumb']['name']));
		$dir = PHPMPS_ROOT . 'data/com/thumb';
		if(!is_dir($dir)) {
			if(@mkdir(rtrim($dir,'/'), 0777))@chmod($dir, 0777);
		}
		$to = $dir.'/'. $thumb_name;
		CreateSmallImage($_FILES['thumb']['tmp_name'], $to, $CFG['com_thumbwidth'], $CFG['com_thumbheight']);
		$image = 'data/com/thumb/'. $thumb_name;
		$db->query("update {$table}com set thumb='$image' where comid='$id' ");
	}

	//上传企业展示图片
	$count = count($_FILES)-1;
	for($i=1;$i<=$count;$i++)
	{
		$exname = strtolower(trim(substr(strrchr($_FILES['file'. $i]['name'], '.'), 1)));
		if(!checkupfile($_FILES['file'. $i]['tmp_name']) || !($_FILES['file'. $i]['tmp_name'] != 'none' && $_FILES['file'. $i]['tmp_name'] && $_FILES['file'. $i]['name']) || $_FILES['file'. $i]['size']>'523298' || !in_array($exname,$alled)) {
			continue;
		}
		$name = date('ymdhis');
		for($a = 0;$a < 6;$a++){ $name .= chr(mt_rand(97, 122));}
		$name .= '.' . end(explode('.', $_FILES['file'. $i]['name']));
		$dir = PHPMPS_ROOT . 'data/com/' . date('ymd');
		if(!is_dir($dir)) {
			if(@mkdir(rtrim($dir,'/'), 0777))@chmod($dir, 0777);
		}
		$to = $dir.'/'. $name;
		if(move_uploaded_file($_FILES['file'. $i]['tmp_name'], $to)) {
			$image = 'data/com/' . date('ymd').'/'. $name;
			$db->query("INSERT INTO {$table}com_image (comid,path) VALUES ('$id','$image')");
		}
	}
	$url = url_rewrite('com',array('act'=>'view', 'comid'=>$id));
	showmsg("恭喜您，企业信息发布成功！", $url);
}
?>