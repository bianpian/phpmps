<?php
define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';
require dirname(__FILE__) . '/include/pay.fun.php';

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : '' ;

switch($_REQUEST['act'])
{
	case 'small_map':
		if(isset($_GET['p']) && preg_match("/^[a-z0-9\-\.]+[,][a-z0-9\-\.]+$/", $_GET['p'])) {
			list($_GET['p1'], $_GET['p2']) = explode(',', $_GET['p']);
		}
		$p1 = trim($_GET['p1']);
		$p2 = trim($_GET['p2']);
		if($p1 == 0) $p1 = '';
		if($p2 == 0) $p2 = '';
		$mark = $_GET['mark'] ? 1 : 0;
		$show = ($_GET['show'] && $p1 && $p2) ? 1 : 0;
		$title = $_GET['title'];
		if($charset != 'utf-8' && $title) {
			$title = iconvs('gbk','utf-8',$title);
		}
		$level = is_numeric($_GET['level']) ? $_GET['level'] : (is_numeric($CFG['map_view_level']) ? $CFG['map_view_level'] : 10);
		$width = is_numeric($_GET['width']) ? $_GET['width'] : 500;
		$height = is_numeric($_GET['height']) ? $_GET['height'] : 500;

		if(!$p1 || !$p2) {
			list($p1,$p2) = explode(',',$CFG['map']);
		}
		$mapflag = $CFG['mapflag'] ? $CFG['mapflag'] : 'baidu';
		$version = 1.1;
		include template('map');
	break;

	case 'show':
		$out   = decrypt($_REQUEST['num'], $CFG['crypt']);
		$hight = strlen($out)*10;
		$image = imagecreate($hight, 20);
		$bg    = imagecolorallocate($image, 255, 255, 255);
		$textcolor = imagecolorallocate($image, 55, 55, 55);
		imagestring($image, 5, 0, 3, $out, $textcolor);
		header("Content-type: image/png");
		imagepng($image);
	break;

	case 'chkcode':
		$_SESSION["chkcode"] = "";
		$chkcode = chkcode();
		$_SESSION["chkcode"] = $chkcode;
	break;

	case 'checkcode':
		$json = new Services_JSON;
		$chkcode = $_SESSION["chkcode"];
		$checkcode = trim($_REQUEST['checkcode']);
		if(empty($chkcode) || $chkcode != $checkcode) {
			echo $json->encode('0');
			exit;
		} else {
			echo $json->encode('1');
			exit;
		}
	break;

	case 'ver':
		require PHPMPS_ROOT . 'include/json.class.php';
		$answer = iconvs('utf8', 'gbk', trim($_REQUEST['answer']));
		$vid = intval($_REQUEST['vid']);
		$ver = get_ver();
		$verf = $ver[$vid];
		$a = $answer == $verf['answer'] ? true : false;
		$json = new Services_JSON;
		echo $json->encode($a);
		exit;
	break;
}
?>