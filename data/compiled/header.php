<?php if(!defined('IN_PHPMPS'))die('Access Denied'); ?>	<div id="top_bar">
<div class="change_city">
<?php if(is_array($areas_list)) foreach($areas_list AS $val) { ?>
<a href="<?php echo $val['url'];?>"><?php echo $val['areaname'];?></a>&nbsp;&nbsp;

<?php } ?>

</div>
<div class="site_service">
<?php if($_userid) { ?>
&nbsp;&nbsp;欢迎你：<?php echo $_username;?>
&nbsp;&nbsp;<a href="member.php">[个人中心]</a>
<?php if($_status<=0) { ?>&nbsp;&nbsp;<a href="member.php?act=send_check_email"><font color='red'>[验证邮件]</font></a><?php } ?>
&nbsp;&nbsp;<a href="member.php?act=logout&mid=<?php echo $_userid;?>">[退出]</a>
<?php } else { ?>
<font color="red"><a href="member.php?act=login&refer=<?php echo $PHP_URL;?>">[登录]</a></font>&nbsp;
<font color="red"><a href="member.php?act=register">[注册]</a></font>&nbsp;
<?php } ?>
</div>
</div>
<!-- topBar 结束 -->
<!-- 头部 -->
<div id="header" class="clearfix">
<div class="logo">
<a href="./"><img src="templates/<?php echo $CFG['tplname'];?>/images/logo.gif" /></a></div>
<div class="quick_menu">
<div class="bd">
<form name="form" action="search.php" method="post">
<input type="text" name="keywords" id="keywords" style="height:20px;" size="50"> 
<input type="submit" name="search" style="height:25px" value="&nbsp;搜 索&nbsp;">
</form>
</div>
</div>
<div class="post"><a href="<?php echo $CFG['postfile'];?>"><img src="templates/<?php echo $CFG['tplname'];?>/images/post.gif" alt="免费发布信息" border="0" ></a></div>
</div>
<!-- 头部 结束 -->
<!-- 主导航 -->
<div id="nav">
<div class="nav_zone">
<ul>
<?php if(is_array($nav)) foreach($nav AS $nav) { ?><li><a href="<?php echo $nav['url'];?>" target="<?php echo $nav['target'];?>"><?php echo $nav['navname'];?></a></li>
<?php } ?>

</ul>
</div>
<!-- 公告 -->
<?php if($CFG['annouce']) { ?>
<div class="search_box">
<div class="bd clearfix">
            <div class="location"><b>公告：</b><?php echo $CFG['annouce'];?></div>
</div>
<?php } ?>
<!-- 公告 结束 -->
</div>
<!-- 主导航结束 -->
