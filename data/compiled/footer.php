<?php if(!defined('IN_PHPMPS'))die('Access Denied'); ?>	<!-- 页脚 -->
<div id="footer" class="clearfix">
  <div class="foot_info">
    <p class="foot_nav">
<a onclick="this.style.behavior='url(#default#homepage)';this.setHomePage('<?=$CFG[weburl]?>');return(false);" style="cursor:pointer;">设为首页</a> | 
<a href=javascript:window.external.AddFavorite('<?php echo $CFG['weburl'];?>','<?php echo $CFG['webname'];?>')>加为收藏</a> |  <a href="./wap">wap</a> | 

<?php if(is_array($about)) foreach($about AS $key => $val) { ?>
<a href=<?php echo $val['url'];?> target=_blank><?php echo $val['title'];?></a>
<?php if($key<(count($about)-1)) { ?> | <?php } ?>

<?php } ?>

</p>
    Powered by <a href=http://www.phpmps.com target=_blank><strong>Phpmps</strong></a>&copy; 2008-2009 Phpmps Inc. 
<?php if(!empty($CFG['qq'])) { ?>
QQ:	
<?php if(is_array($CFG['qq'])) foreach($CFG['qq'] AS $qq) { ?>
<a href="http://wpa.qq.com/msgrd?V=1&amp;Uin=<?php echo $qq;?>&amp;Site=<?php echo $CFG['webname'];?>&amp;Menu=yes" target="_blank"><img style="display:inline" src="http://wpa.qq.com/pa?p=1:<?php echo $qq;?>:4" height="16" border="0" alt="QQ" /><?php echo $qq;?></a>

<?php } ?>

<?php } ?><br />  
<?php echo $CFG['copyright'];?>&nbsp;&nbsp; ICP备案号：<a href=http://www.miibeian.gov.cn target=_blank><?php echo $CFG['icp'];?></a>&nbsp;&nbsp; <?php if($CFG['count']) { ?>
<?php echo $CFG['count'];?>
<?php } ?></div>
  <a href="rss.php"><img src="templates/<?php echo $CFG['tplname'];?>/images/rss_xml.gif" border="0" /></a>
  <div class="clear"></div>
  <div class="bor"></div>
</div>
<!-- 页脚 结束 -->