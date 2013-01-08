<?php
define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.inc.php';

$seo['title'] = $CFG['webname'] . ' - Powered by Phpmps';
$seo['keywords'] = $CFG['keywords'];
$seo['description'] = $CFG['description'];

$cats  = get_cat_list();//所有分类
$new_info  = get_info('','','10','','date','10');//最新信息
if(!empty($new_info)) {
	foreach ($new_info as $val) {
		$val['title'] = encode_output($val['title']);
		$val['areaname'] = encode_output($val['areaname']);
	}
}

$seo['title'] = $CFG['webname'] . ' - Powered by Phpmps';
include tpl('index');
?>