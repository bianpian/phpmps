<?php

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';
require PHPMPS_ROOT . 'include/json.class.php';
require PHPMPS_ROOT . 'include/pay.fun.php';
$act = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'select' ;

$ip = get_ip();
$postarea = getPostArea($ip);
onlyarea($postarea);

if(empty($CFG['visitor_post']) && empty($_userid)) {
	showmsg('游客不允许发布信息，请登陆后发布','member.php?act=login&refer='.$PHP_URL);
}

$CFG['posttimelimit'] = 50;
if((time() - $_COOKIE['lastposttime']) < $CFG['posttimelimit']) {
	showmsg('您发布的太快了，休息一下吧！');
}
if($_userid){
	$member = member_info($_userid);
	if($member['status']!=1) showmsg('您尚未通过审核或被锁定！');
	if((time() - $_lastposttime) < $CFG['posttimelimit']) {
		showmsg('您发布的太快了，休息一下吧！');
	}
}

if($act == 'select')
{
	$cats = get_cat_list();
	$here = array('name'=>'选择分类', 'url'=>'post.php');
	
	$seo['title'] = '选择分类' . ' - Power'.'ed by Php'.'mps';
	$seo['keywords'] = $CFG['keywords'];
	$seo['description'] = $CFG['description'];
	
	include template('select');
}
elseif($act == 'post')
{
	$catid = intval($_REQUEST['id']);
	if(empty($catid)) {
		showmsg('没有选择分类');
	}

	$catinfo = get_cat_info($catid);
	if(empty($catinfo)) showmsg('不存在此分类');
	
	$verf = get_one_ver();
	$member = member_info($_userid);
	$custom = cat_post_custom($catid);
	$mappoint = $CFG['map'] ? explode(',', $CFG['map']) : '';
	
	$seo['title'] = '发布信息 - Powered by Phpmps';
	$seo['keywords'] = $CFG['keywords'];
	$seo['description'] = $CFG['description'];

	include template('post');
}
elseif($act == 'postok')
{
	$catid     = $_POST['catid'] ? intval($_POST['catid']) : '';
	$title     = $_POST['title'] ? htmlspecialchars(trim($_POST['title'])) : '';
	$areaid    = $_POST['areaid'] ? intval($_POST['areaid']) : '';
	$postdate  = time();
	$enddate   = $_POST['enddate']>0 ? (intval($_POST['enddate']*3600*24)) + time() : '0';
	$content   = $_POST['content'] ? htmlspecialchars(trim($_POST['content'])) : '';
	$keywords  = $_POST['keyword'] ? htmlspecialchars(trim($_POST['keyword'])) : '';
	$description = cut_str($content,100);
	$linkman   = $_POST['linkman'] ? htmlspecialchars(trim($_POST['linkman'])) : '';
	$phone     = $_POST['phone'] ? trim($_POST['phone']) : '';
	$qq        = $_POST['qq'] ? intval($_POST['qq']) : '';
	$email     = $_POST['email'] ? htmlspecialchars(trim($_POST['email'])) : '';
	$password  = $_POST['password'] ? trim($_POST['password']) : '';
	$address   = $_POST['address'] ? trim($_POST['address']) : '';
	$mappoint  = $_POST['mappoint'] ? trim($_POST['mappoint']) : '';
	$checkcode = $_POST['checkcode'] ? trim($_POST['checkcode']) : '';
	$number    = $_POST['number'] ? intval($_POST['number']) : '';
	$top_type  = $_POST['top_type'] ? intval($_POST['top_type']) : '';
	$is_type   = $_POST['is_top'] ? intval($_POST['is_top']) : '';
	$is_check  = $CFG['post_check'] == '1' ?  '0' : '1';
	$title = censor($title);
	$content = censor($content);

    if(empty($title)) showmsg("标题不能为空");
	if($areaid<=0) showmsg('请选择地区');
	if(empty($_userid)) {
		if(empty($password)) showmsg("请填写密码");
	}
    if(empty($phone) && empty($qq) && empty($email))showmsg("联系方式必须填写一项");

	check_ver(intval($_REQUEST['vid']), trim($_REQUEST['answer']));
	
	$so = " ip = '$ip' ";
	if(!empty($phone))  $so .= " or phone = '$phone' ";
	if(!empty($qq))     $so .= " or qq = '$qq' ";
	if(!empty($email))  $so .= " or email = '$email' ";
	if(!empty($linkman))$so .= " or linkman = '$linkman' ";

	//是否超出每天发布数量
	if(!empty($CFG['maxpost'])) {
		if($_userid) {
			$sql = "select count(*) from {$table}info where userid='$_userid' and postdate > " .mktime(0,0,0);
		} else {
			$sql = "select count(*) from {$table}info where postdate > " .mktime(0,0,0)." and ($so)";
		}
		if($db->getOne($sql) >= $CFG['maxpost']) showmsg("每天最多发布 $CFG[maxpost] 条信息");
	}
	
	//判断是否重复发布信息
	if($_userid) {
		$sql = "select count(*) from {$table}info where title='$title' and userid='$_userid' and postdate > " .mktime(0,0,0);
	} else {
		$sql = "select count(*) from {$table}info where title='$title' and ($so) and postdate > " .mktime(0,0,0);
	}
	if($db->getOne($sql) > 0) showmsg('请不要重复发布信息');
	
	//处理置顶
	if(!empty($is_top) && !empty($number)) {

		$gold = getUserGlod($_userid);
		if($gold < $CFG['top_gold'] * $number) {
			$is_top = '';
			$top_type  = '';
		} else {
			gold_diff($_username, $CFG['info_top_gold']*$number, '置顶扣除信息币');
			$is_top = time() + $number*3600*24;
		}
	}
	$items = array(
		'userid' => $_userid,
		'catid'  => $catid,
		'areaid' => $areaid,
		'title'  => $title,
		'keywords' => $keywords,
		'description' => $description,
		'content' => $content,
		'linkman' => $linkman,
		'email' => $email,
		'qq' => $qq,
		'phone' => $phone,
		'password' => $password,
		'postarea' => $postarea,
		'postdate' => $postdate,
		'mappoint' => $mappoint,
		'address' => $address,
		'enddate' => $enddate,
		'ip' => $ip,
		'is_check' => $is_check,
		'is_top' => $is_top,
		'top_type' => $top_type,
	);
	$id = addInfo($items, $_POST['cus_value']);

	foreach($_FILES as $key=>$val) {

		$alled = array('png','jpg','gif','jpeg');
		$exname = strtolower(trim(substr(strrchr($val['name'], '.'), 1)));
		if(!checkupfile($val['tmp_name']) || !($val['tmp_name'] != 'none' && $val['tmp_name'] && $val['name']) || $val['size']>'523298' || !in_array($exname,$alled)){
			continue;
		}
		$name = date('ymdhis');
		for($a = 0;$a < 6;$a++){ $name .= chr(mt_rand(97, 122));}
		$thumb_name = $name.'_thumb'. '.' . end(explode('.', $val['name']));
		$name .= '.' . end(explode('.', $val['name']));
		
		$dir = PHPMPS_ROOT . 'data/infoimage/' . date('ymd');
		if(!is_dir($dir)) {
			if(@mkdir(rtrim($dir,'/'), 0777))@chmod($dir, 0777);
		}
		$to = $dir.'/'. $name;

		if(move_uploaded_file($val['tmp_name'], $to)) {
			$image = 'data/infoimage/' . date('ymd').'/'. $name;
			@chmod(PHPMPS_ROOT.$image, 0777);
			$db->query("INSERT INTO {$table}info_image (infoid,path) VALUES ('$id','$image')");
		}
		if(!$do) {
			$thumbimg = 'data/infoimage/' . date('ymd').'/'.$thumb_name;
			$thumb = CreateSmallImage( $image, $thumbimg, 154, 134);
			@chmod(PHPMPS_ROOT.$thumbimg, 0777);
			$db->query("UPDATE {$table}info SET thumb='$thumbimg' WHERE id='$id' ");
			$do = true;
		}
	}
	
	//奖励积分
	if($_username) {
		$credit_count = getCreditTimes($_username, 'post_info_credit');
		if($credit_count < $CFG['max_info_credit']) {
			if(!empty($CFG['post_info_credit'])) credit_add($_username, $CFG['post_info_credit'], 'post_info_credit');
		}
		$query = $db->query("UPDATE {$table}member SET lastposttime=".time()." WHERE userid='$_userid' ");
	}

	//设置最后发布时间的cookie
	setcookie('lastposttime', time(), time()+86400*24);

	$url = url_rewrite('view', array('vid'=>$id));
	include template('post_ok');
}
?>