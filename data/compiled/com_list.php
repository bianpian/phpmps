<?php if(!defined('IN_PHPMPS'))die('Access Denied'); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
<title><?php echo $seo['title'];?></title>
<meta name="Keywords" content="<?php echo $seo['keywords'];?>">
<meta name="Description" content="<?php echo $seo['description'];?>">
<link href="templates/<?php echo $CFG['tplname'];?>/style/reset.css" type="text/css" rel="stylesheet" />
<link href="templates/<?php echo $CFG['tplname'];?>/style/style.css" type="text/css" rel="stylesheet" />
<link href="templates/<?php echo $CFG['tplname'];?>/style/com.css" type="text/css" rel="stylesheet" />
<script src="js/common.js"></script>
<script src="js/jquery.js"></script>
</head>
<body class="home-page">
<div class="wrapper">

<?php include template(header); ?>

<!-- 主体 -->
<div class=divline></div>
<div id="content">

<?php include template(here); ?>

<div class="banner_shop"><!--广告--></div>
<div class="clearfix">
<div class="col_main">
<div class="shop_module">
<?php if($area_arr) { ?>
<div class="address">区域查找：
<?php if(is_array($area_arr)) foreach($area_arr AS $val) { ?>
<a href=<?php echo $val['url'];?>><?php echo $val['areaname'];?></a>&nbsp;

<?php } ?>

</div>
<?php } ?>
<div class="bd">
<ul class="clearfix">
<?php if(is_array($articles)) foreach($articles AS $val) { ?>
<li>
  <div class="list_module">
<div class="pic"><a href="<?php echo $val['url'];?>" target="_blank"><img src="<?php echo $val['thumb'];?>" alt="<?php echo $val['comname'];?>" border="0"  width="140" height="60"/></a></div>
<b><a href="<?php echo $val['url'];?>" target="_blank"><?php echo $val['sname'];?></a></b> <span class="cont"><?php echo $val['introduce'];?>...</span>
  </div>
</li>

<?php } ?>

</ul>
</div>
  <div class="pagination_module clearfix">
  <span class="right">
<?php include template(page); ?>
</span>					</div>
</div>
</div>
<div class="col_sub">
<div class="categories_nav">
<div class="hd">商家分类导航</div>
<div class="bd">
<ul>
<li><a href="postcom.php" ><img src="templates/<?php echo $CFG['tplname'];?>/images/postcom.gif"></a>
</li>
<?php if(is_array($com_cats)) foreach($com_cats AS $key=>$cat) { ?>
<li><a href="javascript:void(<?php echo $key;?>);" class="xias" onclick="showHide(this,'items<?php echo $key;?>');"><?php echo $cat['catname'];?></a>
  <ul id="items<?php echo $key;?>" style="display: block;">
<?php if(!empty($cat[children])) { ?>		
<?php if(is_array($cat[children])) foreach($cat[children] AS $chi) { ?>
<li><a href="<?php echo $chi['url'];?>" ><?php echo $chi['name'];?></a></li>

<?php } ?>

<?php } ?>
  </ul>
</li>

<?php } ?>

</ul>
</div>
</div>
<!--
<div style=" margin-top:5px;border:1px solid #f36100; padding:10px; padding-top:5px; text-align:left;">
XXXXXXXXXXXX
</div>
-->
</div>
</div>
</div>
<!-- 主体 结束 -->

<?php include template(footer); ?>

</div>