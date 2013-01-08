<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('sitemap');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

if($_REQUEST['act']=='list')
{
	include tpl('sitemap');
}
elseif ($_REQUEST['act']=='sitemap')
{
    require_once 'include/sitemaps.class.php';
	
	$today  = date('Y-m-d');
	$domain = $CFG['weburl'];
    //$domain = PHPMPS_URL;

    $sm  =& new google_sitemap();
    $smi =& new google_sitemap_item($domain, $today, 'hourly', '0.9');
    $sm->add_item($smi);

    $sql = "SELECT catid FROM {$table}category ORDER BY parentid";
    $res = $db->query($sql);
    while($row = $db->fetchRow($res)) {
		$url = $domain .'/'. url_rewrite('category', array('cid' => $row['catid']));
        $smi =& new google_sitemap_item( $url, $today, 'hourly', '0.9');
        $sm->add_item($smi);
    }
    $sql = "SELECT id FROM {$table}info WHERE is_check = 1";
    $res = $db->query($sql);
    while($row = $db->fetchRow($res)) {
		$url = $domain .'/'. url_rewrite('view', array('vid' => $row['id']));
        $smi =& new google_sitemap_item( $url, $today, 'weekly', '0.9');
        $sm->add_item($smi);
    }

    $sm_file = PHPMPS_ROOT.'sitemap.xml';
    if($sm->build($sm_file)) {
        show('生成sitemap成功。', 'sitemap.php');
    } else {
        show('生成sitemap失败。', 'sitemap.php');
    }
}

?>