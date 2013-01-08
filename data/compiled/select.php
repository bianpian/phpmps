<?php if(!defined('IN_PHPMPS'))die('Access Denied'); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
<title><?php echo $seo['title'];?></title>
<meta name="Keywords" content="<?php echo $seo['keywords'];?>">
<meta name="Description" content="<?php echo $seo['description'];?>">
<link href="templates/<?php echo $CFG['tplname'];?>/style/reset.css" type="text/css" rel="stylesheet" />
<link href="templates/<?php echo $CFG['tplname'];?>/style/style.css" type="text/css" rel="stylesheet" />
<link href="templates/<?php echo $CFG['tplname'];?>/style/post.css" type="text/css" rel="stylesheet" />
<script src="js/common.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<body class="home-page">
<div class="wrapper">

<?php include template(header); ?>

<!-- 主体 -->
<div id="content">
<div class="thd clearfix"><b>发布步骤：</b><span class="current">1.选择分类</span><span>2.填写内容</span><span>3.发布完成</span></div>
<div class="fbd">
<div class="tips">
1、只允许发布<b class="red_skin"><?php echo $city;?></b>本地相关信息<br />
2、请不要肆意发布垃圾信息、虚假信息、重复信息<br />
3、所有信息发布必须严格遵守中华人民共和国所有法律法规及本地、本行业相关规定，严禁发布带有任何违法或违规色彩的信息<br />
4、信息发布者必须自行对信息的有效性、真实性承担一切责任
</div>
<div class="infophpmps">
<?php if(is_array($cats)) foreach($cats AS $cat) { ?>
<ul class="clearfix">
<div class="infobt"><?php echo $cat['catname'];?>：</div>
<?php if(!empty($cat[children])) { ?>
<?php if(is_array($cat[children])) foreach($cat[children] AS $chi) { ?>
<li><a href="<?php echo $CFG['postfile'];?>?act=post&id=<?=$chi['id']?>" ><?php echo $chi['name'];?></a></li>

<?php } ?>

<?php } ?>
</ul>

<?php } ?>

</div>
</div>
</div>
<!-- 主体 结束 -->

<?php include template(footer); ?>

</div><div id="mask" style="display:none"></div>

</body>
</html>
