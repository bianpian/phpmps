<?php if(!defined('IN_PHPMPS'))die('Access Denied'); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
<title><?php echo $seo['title'];?></title>
<meta name="Keywords" content="<?php echo $seo['keywords'];?>">
<meta name="Description" content="<?php echo $seo['description'];?>">
<link href="templates/<?php echo $CFG['tplname'];?>/style/index.css" type="text/css" rel="stylesheet" />
<script src="js/common.js"></script>
</head>
<body class="home-page">
<div class="wrapper">

<?php include template(header); ?>

<!-- 主体 -->
<div id="content">
<!-- 第一屏 -->
<div class="grid_c1 clearfix">
<div class="col_main">
<div class="clearfix">
<div class="left">

<!-- 分类列表 -->
<div class="category_list">
<span class="bk_tp"></span>
<div class="bd">
<div class="weather_changeColor">
<!-- 列表 -->
<div class="category_bd">
<?php if(is_array($cats_list)) foreach($cats_list AS $cat) { ?>
<h3><a href="<?php echo $cat['caturl'];?>"><?php echo $cat['catname'];?></a></h3>
<ul class="clearfix">
<?php if(is_array($cat[children])) foreach($cat[children] AS $child) { ?>
<li><a href="<?php echo $child['url'];?>" ><?php echo $child['name'];?>(<?php echo $info_count[$child['id']];?>)</a></li>

<?php } ?>

</ul>

<?php } ?>

</div>
</div>
</div>
<span class="bk_ft"></span>
</div>
</div>
<div class="right">
<!-- 切换banner -->
<div class="container" id="idTransformView">
<script type=text/javascript>
<!--
var focus_width=350
var focus_height=170
var text_height=0
var swf_height = focus_height+text_height

var pics='<?=$flash['image']?>'
var links='<?=$flash['url']?>'
var texts='||||'	
document.write('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="'+ focus_width +'" height="'+ swf_height +'">');
document.write('<param name="allowScriptAccess" value="sameDomain"><param name="movie" value="images/flashplay.swf"><param name="quality" value="high"><param name="bgcolor" value="#ffffff">');
document.write('<param name="menu" value="false"><param name="wmode" value="opaque">');
document.write('<param name="FlashVars" value="pics='+pics+'&links='+links+'&texts='+texts+'&borderwidth='+focus_width+'&borderheight='+focus_height+'&textheight='+text_height+'">');
document.write('<embed src="images/flashplay.swf" wmode="opaque" FlashVars="pics='+pics+'&links='+links+'&texts='+texts+'&borderwidth='+focus_width+'&borderheight='+focus_height+'&textheight='+text_height+'" menu="false" bgcolor="#ffffff" quality="high" width="'+ focus_width +'" height="'+ focus_height +'" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />');		
document.write('</object>');
//-->
</script>
</div>
<!-- 推荐信息 -->
<div class="news_module">
<div class="hd"><span class="tit info">推荐信息</span></div>
<div class="bd">
<ul>
<?php if(is_array($pro_info)) foreach($pro_info AS $val) { ?>
<li><a href="<?php echo $val['url'];?>"  target=_blank><?php echo $val['title'];?></a><span><?php echo $val['catname'];?></span> -<span><?php echo $val['areaname'];?></span></li>

<?php } ?>

</ul>
</div>
<div class="ft">
</div>
</div>
<!-- 最新新闻 -->
<div class="news_module">
<div class="hd"><span class="tit news">最新信息</span></div>
<div class="bd bd_c">
<ul>
<?php if(is_array($new_info)) foreach($new_info AS $val) { ?>
<li><a href="<?php echo $val['url'];?>" target=_blank><?php echo $val['title'];?></a><span><?php echo $val['catname'];?></span> -<span><?php echo $val['areaname'];?></span></li>

<?php } ?>

</ul>
</div>
</div>
</div>
</div>
<!-- 图片信息开始 -->
<div class="tupxx">
<div class="tupxxbt1"><span class="tupxxbt">图片信息</span></div>
<div class="tupxxnr clearfix">
<ul>
<?php if(is_array($thumb_info)) foreach($thumb_info AS $thumb) { ?>
<li><a href="<?php echo $thumb['url'];?>" title="<?php echo $thumb['title'];?>" target=_blank><b><img src="<?php echo $thumb['thumb'];?>" alt="<?php echo $thumb['title'];?>" /></b>
<span class="title"><?php echo $thumb['title'];?></span></a><span class="price"><?php echo $thumb['postdate'];?></span></li>

<?php } ?>

</ul>
</div>
</div>
<!--*图片信息结束*-->
<!-- 企业信息开始 -->
<div class="tupxx">
<div class="tupxxbt1"><span class="tupxxbt">企业展示</span></div>
<div class="tupxxnr clearfix">
<ul>
<?php if(is_array($coms)) foreach($coms AS $val) { ?>
<li><a href="<?php echo $val['url'];?>" title="<?php echo $val['comname'];?>" target=_blank><b><img src="<?php echo $val['thumb'];?>" alt="<?php echo $val['comname'];?>" /></b>
<span class="title"><?php echo $val['sname'];?></span></a><span class="price"><?php echo $val['postdate'];?></span></li>

<?php } ?>

</ul>
</div>
</div>
<!--*图片信息结束*-->
</div>
<div class="col_sub">
<!--今日资讯-->
<div class="site_notice">
<div class="hd"><div class="tit">最新资讯</div></div>
<div class="bd">
    <ul>
<?php if(is_array($articles)) foreach($articles AS $val) { ?>
<li><a href="<?php echo $val['url'];?>" target="_blank" title="<?php echo $val['title'];?>">&nbsp;<?php echo $val['ctitle'];?></a></li>

<?php } ?>

</ul>
</div>
<div class="ft"><a href="article.php" class="more">更多</a></div>
</div>
<!--热门信息-->
<div class="site_hot">
<div class="hd"><div class="tits">热门信息</div></div>
<div class="bd">
<ul>
<?php if(is_array($hot_info)) foreach($hot_info AS $val) { ?>
<li>[<?php echo $val['postdate'];?>] <a href="<?php echo $val['url'];?>" title="<?php echo $val['title'];?>" target=_blank><?php echo $val['title'];?></a></li>

<?php } ?>

</ul>
</div>
</div>
<!-- 评论开始 -->
<div class="seller_rank">
<div class="hd"><span class="tit">评论</span></div>
<div class="bd">
<?php if(is_array($comments)) foreach($comments AS $comment) { ?>
<div class="pingl">
<div class="pinglnr"><?php echo $comment['content'];?></div>
<div class="pinglbt"><b><?php echo $comment['username'];?></b>&nbsp;对&nbsp;<a href="<?php echo $comment['url'];?>" target="_blank" class="pinglbt2"><?php echo $comment['title'];?></a>&nbsp;的评论</div>
</div>

<?php } ?>

</div>
<div class="ft"></div>
</div>
<!-- 评论结束 -->
<!-- 新手+客服 -->
<div class="tiro_service">
<div class="tiro_zone">
<div class="hd"><span class="tit">新手上路</span></div>
<div class="bd">
<ul>
<?php if(is_array($helps)) foreach($helps AS $help) { ?>
<li><a href="<?php echo $help['url'];?>" target="_blank"><?php echo $help['title'];?></a></li>

<?php } ?>

</ul>
</div>
<div class="ft"><a href="help.php?act=list" class="more">更多 </a></div>
</div>
<div class="service_zone">
<div class="hd"><span class="tit">客服中心</span></div>
<div class="bd">
<ul>
<li class="ser_tel">热线电话：<?php echo $CFG['phone'];?></li>
<li class="ser_qq">QQ：<?php if(is_array($CFG['qq'])) foreach($CFG['qq'] AS $qq) { ?><a href="http://wpa.qq.com/msgrd?V=1&amp;Uin=<?php echo $qq;?>&amp;Site=<?php echo $CFG['webname'];?>&amp;Menu=yes" target="_blank"><img style="display:inline" src="http://wpa.qq.com/pa?p=1:<?php echo $qq;?>:4" height="16" border="0" alt="QQ" /><?php echo $qq;?></a>

<?php } ?>
</li>
<li class="ser_qqs">QQ群：<?php echo $CFG['qqun'];?></li>
</ul>
</div>
</div>
</div>
<!-- 新手+客服 -->
</div>
</div>
<!-- 第二屏 -->
<div style=" margin-top:5px;"></div>
<div class="grid_c2">
<!-- 便民电话 -->
<div class="live_box clearfix">
<h2><a href="bianmin.php">便民电话</a></h2>
<div class="p_right">
<ul class="fb">
<?php if(is_array($fac)) foreach($fac AS $fac) { ?> <li><?php echo $fac['title'];?>: <?php echo $fac['phone'];?></li> 
<?php } ?>

</ul>
</div>
</div>
<!-- 便民电话结束 -->
<!-- 友情链接 -->
<div class="friend_link">
<div class="hd">友情链接</div>
<div class="bd">
<ul class="pic clearfix">
<?php if(!empty($links[image])) { ?>
<?php if(is_array($links[image])) foreach($links[image] AS $link) { ?>
<li><a href="<?php echo $link['url'];?>" target=_blank ><img src="<?php echo $link['logo'];?>" alt="<?php echo $link['web_name'];?>" width="88" height="31" border="0" title="<?php echo $link['webname'];?>"></a></li>

<?php } ?>

<?php } ?>
</ul>
<ul class="txt clearfix">
<?php if(!empty($links[txt])) { ?>
<?php if(is_array($links[txt])) foreach($links[txt] AS $link) { ?>
<li><a href="<?php echo $link['url'];?>" target=_blank title="<?php echo $link['webname'];?>"><?php echo $link['webname'];?></a></li>

<?php } ?>

<?php } ?>
</ul>
</div>
<div class="ft"></div>
</div>
<!-- 友情链接结束 -->
</div>
</div>
<!-- 主体 结束 -->

<?php include template(footer); ?>

</div>
</body>
</html>