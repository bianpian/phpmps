<?php

define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';
require PHPMPS_ROOT . '/include/rss.class.php';

$cat  = isset($_REQUEST['catid']) ? ' AND i.catid in (' . get_cat_children(intval($_REQUEST['catid'])).')' : '';
$uri  = PHPMPS_PATH;

$webname = htmlspecialchars($CFG['webname']);
$desc  = htmlspecialchars($CFG['description']);
$image =  $uri.'templates/'.$CFG['tplname'].'/images/logo.gif';

$rss = new RSSBuilder($charset, $uri, $webname, $desc, $image);
$rss->addDCdata('', 'http://www.phpmps.com', date('r'));
$rss->addSYdata('', 'http://www.phpmps.com', date('Y'));

$sql = "select i.id,i.title,i.postdate,i.description,c.catid,c.catname from {$table}info as i left join {$table}category as c on i.catid = c.catid where i.is_check = 1 $cat order by i.id desc limit 30";
if($res = $db->query($sql)) {
	while($row=$db->fetchRow($res)) {
		$item_url  = url_rewrite('view', array('vid' => $row['id']));
        $separator = (strpos($item_url, '?') === false)? '?' : '&amp;';
        $about     = $uri . $item_url;
        $title     = htmlspecialchars($row['title']);
        $link      = $uri . $item_url . $separator . 'from=rss';
        $desc      = htmlspecialchars($row['desc']);
        $subject   = htmlspecialchars($row['catname']);
        $date      = date('Y-m-d H:i:s' , $row['postdate']);

        $rss->addItem($about, $title, $link, $desc, $subject, $date);
	}
	$version = '2.0';
	$rss->outputRSS($version);
}
?>