DROP TABLE IF EXISTS `phpmps_about`;
CREATE TABLE `phpmps_about` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `keywords` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `postdate` int(11) NOT NULL,
  `url` varchar(100) NOT NULL,
  `aboutorder` smallint(5) NOT NULL default '0',
  `is_show` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `is_show` (`is_show`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_admin`;
CREATE TABLE `phpmps_admin` (
  `userid` smallint(5) unsigned NOT NULL auto_increment,
  `username` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `truename` varchar(30) NOT NULL,
  `email` varchar(35) NOT NULL,
  `purview` text NOT NULL,
  `is_admin` tinyint(1) NOT NULL, 
  `lastip` varchar(15) NOT NULL,
  `lastlogin` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`userid`),
  KEY `username` (`username`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_admin_log`;
CREATE TABLE `phpmps_admin_log` (
  `logid` int(10) unsigned NOT NULL auto_increment,
  `adminname` varchar(32) NOT NULL,
  `logdate` int(10) unsigned NOT NULL,
  `logtype` varchar(255) NOT NULL,
  `logip` varchar(15) NOT NULL,
  PRIMARY KEY  (`logid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_ads`;
CREATE TABLE IF NOT EXISTS `phpmps_ads` (
  `adsid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `placeid` smallint(5) unsigned NOT NULL,
  `adsname` varchar(32) NOT NULL,
  `adstype` tinyint(3) NOT NULL,
  `adsurl` varchar(150) NOT NULL,
  `adscode` text NOT NULL,
  `addtime` int(11) unsigned NOT NULL,
  `updatetime` int(11) NOT NULL,
  `linkman` varchar(32) NOT NULL,
  PRIMARY KEY (`adsid`),
  KEY `adsname` (`adsname`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_ads_place`;
CREATE TABLE IF NOT EXISTS `phpmps_ads_place` (
  `placeid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `placename` varchar(32) NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `introduce` varchar(100) NOT NULL,
  PRIMARY KEY (`placeid`),
  KEY `placename` (`placename`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_area`;
CREATE TABLE `phpmps_area` (
  `areaid` mediumint(6) NOT NULL auto_increment,
  `areaname` varchar(32) NOT NULL,
  `parentid` int(11) unsigned NOT NULL,
  `areaorder` smallint(6) NOT NULL,
  PRIMARY KEY  (`areaid`),
  KEY `areaname` (`areaname`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_article`;
CREATE TABLE IF NOT EXISTS `phpmps_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `typeid` smallint(5) NOT NULL,
  `title` varchar(100) NOT NULL,
  `keywords` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `thumb` varchar(50) NOT NULL,
  `listorder` smallint(5) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL,
  `is_index` tinyint(1) unsigned NOT NULL,
  `is_pro` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_index` (`is_index`),
  KEY `addtime` (`addtime`),
  KEY `is_pro` (`is_pro`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_category`;
CREATE TABLE `phpmps_category` (
  `catid` mediumint(6) NOT NULL auto_increment,
  `catname` varchar(32) NOT NULL,
  `keywords` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `parentid` int(11) default NULL,
  `catorder` smallint(6) NOT NULL,
  `cattplname` varchar(30) NOT NULL,
  `viewtplname` varchar(30) NOT NULL,
  PRIMARY KEY  (`catid`),
  KEY `parentid` (`parentid`),
  KEY `catname` (`catname`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_com`;
CREATE TABLE IF NOT EXISTS `phpmps_com` (
  `comid` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL,
  `catid` smallint(5) unsigned NOT NULL,
  `areaid` smallint(5) unsigned NOT NULL,
  `comname` varchar(100) NOT NULL,
  `keywords` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `thumb` varchar(50) NOT NULL,
  `introduce` text,
  `phone` varchar(15) NOT NULL,
  `linkman` varchar(32) NOT NULL,
  `qq` varchar(15) NOT NULL,
  `msn` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `fax` varchar(15) NOT NULL,
  `address` varchar(100) NOT NULL,
  `hours` varchar(50) NOT NULL,
  `mappoint` varchar(16) NOT NULL,
  `is_check` tinyint(1) unsigned NOT NULL,
  `click` int(11) NOT NULL,
  `postdate` int(11) unsigned NOT NULL,
  PRIMARY KEY (`comid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_comment`;
CREATE TABLE `phpmps_comment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `infoid` mediumint(8) unsigned NOT NULL default '0',
  `userid` int(11) unsigned NOT NULL,
  `username` varchar(60) NOT NULL,
  `content` text NOT NULL,
  `is_check` tinyint(1) unsigned NOT NULL,
  `postdate` int(10) unsigned NOT NULL default '0',
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `infoid` (`infoid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_com_cat`;
CREATE TABLE IF NOT EXISTS `phpmps_com_cat` (
  `catid` mediumint(6) NOT NULL AUTO_INCREMENT,
  `catname` varchar(32) NOT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parentid` int(11) DEFAULT NULL,
  `catorder` smallint(6) NOT NULL,
  PRIMARY KEY (`catid`),
  KEY `parentid` (`parentid`),
  KEY `catname` (`catname`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_com_image`;
CREATE TABLE IF NOT EXISTS `phpmps_com_image` (
  `imgid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `comid` int(11) unsigned NOT NULL,
  `path` varchar(100) NOT NULL,
  PRIMARY KEY (`imgid`),
  KEY `infoid` (`comid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_config`;
CREATE TABLE `phpmps_config` (
  `setname` varchar(100) NOT NULL,
  `value` text,
  KEY `setname` (`setname`)
) TYPE=MyISAM ;

INSERT INTO `phpmps_config` (`setname`, `value`) VALUES
('webname', 'php分类信息'),
('weburl', ''),
('keywords', ''),
('copyright', 'php分类信息 版权所有'),
('description', ''),
('banwords', ''),
('icp', ''),
('qq', ''),
('post_check', ''),
('comment_check', ''),
('count', ''),
('tplname', 'phpmps'),
('crypt', ''),
('maxpost', '15'),
('annouce', ''),
('rewrite', '0'),
('onlyarea', ''),
('map', ''),
('del_m_info', '1'),
('del_m_comment', '1'),
('pagesize', '20'),
('uc', '0'),
('uc_api', 'http://localhost/ucenter'),
('uc_appid', '1'),
('uc_key', 'phpmps'),
('uc_dbhost', 'localhost'),
('uc_dbname', 'ucenter'),
('uc_dbuser', 'root'),
('uc_dbpwd', ''),
('uc_dbpre', 'uc_'),
('uc_charset', 'gbk'),
('expired_view', '0'),
('visitor_post', '1'),
('visitor_view', '1'),
('visitor_comment', '1'),
('closesystem', '0'),
('close_tips', '网站维护，暂时关闭，请稍后访问。'),
('postfile', 'post.php'),
('sendmailtype', ''),
('smtphost', ''),
('smtpuser', ''),
('smtppass', ''),
('smtpport', ''),
('info_top_gold', '1'),
('info_refer_gold', '1'),
('max_login_credit', '2'),
('register_credit', '1'),
('login_credit', '1'),
('post_info_credit', '2'),
('post_comment_credit', '1'),
('credit2gold', '20'),
('money2gold', '1'),
('max_comment_credit', '5'),
('max_info_credit', '5'),
('qqun', ''),
('phone', ''),
('email', ''),
('close_register', '0'),
('reg_check', '0'),
('wap', '1'),
('com_pagesize', '18'),
('mapapi', 'http://api.map.baidu.com/api?v=1.1'),
('mapflag', 'baidu'),
('map_view_level', '15'),
('mapapi_charset', ''),
('com_thumbwidth', '200'),
('com_thumbheight', '80'),
('testemail', '');

DROP TABLE IF EXISTS `phpmps_custom`;
CREATE TABLE IF NOT EXISTS `phpmps_custom` (
  `cusid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `cusname` varchar(60) NOT NULL,
  `custype` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `value` text NOT NULL,
  `unit` varchar(32) NOT NULL,
  `search` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `listorder` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cusid`),
  KEY `catid` (`catid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_cus_value`;
CREATE TABLE IF NOT EXISTS `phpmps_cus_value` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `infoid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `cusid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `cusvalue` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `infoid` (`infoid`),
  KEY `cusid` (`cusid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_facilitate`;
CREATE TABLE IF NOT EXISTS `phpmps_facilitate` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `phone` varchar(13) NOT NULL,
  `introduce` varchar(255) NOT NULL,
  `listorder` smallint(5) unsigned NOT NULL,
  `updatetime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `number` (`phone`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_flash`;
CREATE TABLE `phpmps_flash` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `image` varchar(100) NOT NULL,
  `url` varchar(100) NOT NULL,
  `flaorder` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `image` (`image`),
  KEY `url` (`url`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_help`;
CREATE TABLE IF NOT EXISTS `phpmps_help` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `typeid` smallint(5) NOT NULL,
  `keywords` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `listorder` smallint(5) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL,
  `is_index` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_index` (`is_index`),
  KEY `addtime` (`addtime`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

INSERT INTO `phpmps_help` (`id`, `title`, `typeid`, `keywords`, `description`, `content`, `listorder`, `addtime`, `is_index`) VALUES
(1, '如何注册成为本站会员', 1, '如何注册成为本站会员', '如何注册成为本站会员', '<p><br />\r\n点击网站顶部的注册链接进入填写注册信息页面，填写用户名、密码和邮件,点击注册按钮。</p>\r\n<p>注意： 1. 用户名注册以后是不能更改的&nbsp; 2. 注册用的邮箱是用于密码找回的，所以请尽量填写真实邮箱。</p>', 1, 1283498679, 1),
(2, '如何登录网站', 1, '如何登录网站', '如何登录网站', '<p><br />\r\n点击头部的 [登陆] 链接进入登录页面后,填写用户名和密码进行登录。</p>', 2, 1283499260, 0),
(3, '如何修改密码', 1, '如何修改密码', '如何修改密码', '<p>您可以在个人管理中心中点击【修改密码】的链接进入修改密码页面进行修改。</p>', 3, 1283499376, 0),
(4, '如何找回密码', 1, '如何找回密码', '如何找回密码', '<p><br />\r\n如果您忘记了密码可以使用找回密码操作<br />\r\n<br />\r\n找回密码步骤：<br />\r\n第一步：点击找回密码链接进入填写用户名和邮箱页面。<br />\r\n第二步：填写您注册时输入的用户名和邮箱地址，发送重置密码邮件。<br />\r\n第三步：到邮箱中收信，点击邮件中的&ldquo;重置密码&rdquo;链接，进入重置密码页面<br />\r\n第四步：重新设置您的密码，完成找回密码操作。然后您就可以使用新密码登录了。</p>', 4, 1283499417, 0),
(5, '如何修改个人资料', 1, '如何修改个人资料', '如何修改个人资料', '<p><br />\r\n您可以在个人管理中心中点击&ldquo;修改资料&rdquo;链接进入修改资料页面进行修改。</p>', 5, 1283499492, 1),
(6, '什么是匿名发布', 2, '什么是匿名发布', '什么是匿名发布', '<p>匿名发帖就是不需要登录注册，直接发帖。</p>\r\n<p>匿名发布的信息，唯一的修改凭证就是发布信息时所填写的密码，请牢记这个密码。</p>', 6, 1283499666, 0),
(7, '如何修改信息内容', 2, '如何修改信息内容', '如何修改信息内容', '<p>1.凭密码修改<br />\r\n发布信息的时候会让用户输入一个信息管理密码，您可以在信息详情页输入这个密码进行修改<br />\r\n2.登陆会员中心修改<br />\r\n如果信息是您在登陆状态下发布的，那么您可以登陆会员中心，点击【我的信息】列表，找到具体的信息进行修改，<br />\r\n也可以到信息详情页，点【编辑】进行修改。</p>', 7, 1283499832, 0),
(8, '什么是一键更新信息', 2, '什么是一键更新信息', '什么是一键更新信息', '<p>一键更新信息可以使您的信息自然排在信息的最前端<br />\r\n点击信息详情页的【提升按钮】进行提升，一键更新信息消耗信息币，详情请看<a href=\'member.php?act=credit_rule\'>member.php?act=credit_rule</a></p>', 8, 1283500716, 1),
(9, '如何置顶信息', 2, '如何置顶信息', '如何置顶信息', '<p>发布信息的时候可以选择信息置顶，也可以进入会员中心的信息管理，点击信息右侧的&ldquo;置顶&rdquo;链接进行置顶，信息置顶消耗信息币。</p>', 9, 1283500968, 1),
(10, '什么是积分，有何用处', 3, '什么是积分，有何用处', '什么是积分，有何用处', '<p>积分是对用户注册、登陆、发布信息、发表评论的一种奖励，可以用积分兑换信息币，不用花钱置顶和刷新信息。</p>', 10, 1283501184, 1),
(11, '什么是信息币', 3, '什么是信息币', '什么是信息币', '<p>信息币是本站一种虚拟货币，可以用来对信息进行刷新，置顶。</p>\r\n<p>有两种方式获得信息币，一是用钱购买信息币，二是用积分兑换信息币。</p>', 11, 1283501243, 0),
(12, '积分、信息比和资金可以转让吗？', 3, '积分、信息比和资金可以转让吗？', '积分、信息比和资金可以转让吗？', '<p>均不可以转让，只能供会员自己使用。</p>', 12, 1283501493, 0);

DROP TABLE IF EXISTS `phpmps_info`;
CREATE TABLE IF NOT EXISTS `phpmps_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL,
  `catid` mediumint(6) unsigned NOT NULL,
  `areaid` smallint(5) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `thumb` varchar(50) NOT NULL,
  `keywords` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `linkman` varchar(32) NOT NULL,
  `email` varchar(50) NOT NULL,
  `qq` varchar(15) NOT NULL,
  `phone` varchar(13) NOT NULL,
  `address` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `mappoint` varchar(20) NOT NULL,
  `postarea` varchar(32) NOT NULL,
  `postdate` int(11) NOT NULL,
  `enddate` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `click` smallint(6) unsigned NOT NULL DEFAULT '0',
  `is_pro` int(11) unsigned NOT NULL ,
  `is_top` int(11) unsigned NOT NULL ,
  `top_type` tinyint(1) unsigned NOT NULL,
  `is_check` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `areaid` (`areaid`),
  KEY `postdate` (`postdate`),
  KEY `click` (`click`,`postdate`),
  KEY `is_check` (`is_check`),
  FULLTEXT KEY `keywords` (`keywords`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_info_image;
CREATE TABLE `phpmps_info_image` (
  `imgid` int(11) unsigned NOT NULL auto_increment,
  `infoid` int(11) unsigned NOT NULL,
  `path` varchar(100) NOT NULL,
  PRIMARY KEY  (`imgid`),
  KEY `infoid` (`infoid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_link`;
CREATE TABLE `phpmps_link` (
  `id` int(11) NOT NULL auto_increment,
  `webname` varchar(30) NOT NULL,
  `url` varchar(50) NOT NULL,
  `linkorder` mediumint(6) NOT NULL,
  `logo` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `webname` (`webname`),
  KEY `url` (`url`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

INSERT INTO `phpmps_link` (`id`, `webname`, `url`, `linkorder`, `logo`) VALUES 
(1, 'php分类信息', 'http://www.phpmps.com', 1, 'http://www.phpmps.com/logo.gif');

DROP TABLE IF EXISTS `phpmps_member`;
CREATE TABLE IF NOT EXISTS `phpmps_member` (
  `userid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `username` varchar(32) NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(32) NOT NULL,
  `registertime` int(11) unsigned NOT NULL,
  `registerip` varchar(15) NOT NULL,
  `lastlogintime` int(11) unsigned NOT NULL,
  `lastloginip` varchar(15) NOT NULL,
  `sendmailtime` int(11) NOT NULL,
  `qq` varchar(15) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` varchar(100) NOT NULL,
  `mappoint` varchar(50) NOT NULL,
  `money` float NOT NULL,
  `gold` smallint(5) unsigned NOT NULL,
  `credit` smallint(5) unsigned NOT NULL,
  `lastposttime` int(10) unsigned NOT NULL,
  `status` TINYINT( 1 ) UNSIGNED NOT NULL,
  PRIMARY KEY (`userid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_nav`;
CREATE TABLE `phpmps_nav` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `navname` varchar(32) NOT NULL,
  `url` varchar(100) NOT NULL,
  `target` varchar(6) NOT NULL,
  `navorder` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`navname`),
  KEY `url` (`url`),
  KEY `navorder` (`navorder`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

INSERT INTO `phpmps_nav` (`id`, `navname`, `url`, `target`, `navorder`) VALUES 
(1, '首页', 'index.php', '_self', 1),
(2, '黄页', 'com.php', '_self', 2),
(3, '资讯', 'article.php', '_self', 3);

DROP TABLE IF EXISTS `phpmps_pay`;
CREATE TABLE IF NOT EXISTS `phpmps_pay` (
  `payid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `typeid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `note` char(200) NOT NULL DEFAULT '',
  `paytype` char(20) NOT NULL DEFAULT '',
  `amount` float NOT NULL DEFAULT '0',
  `balance` float NOT NULL DEFAULT '0',
  `year` smallint(4) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `username` char(30) NOT NULL DEFAULT '',
  `ip` char(15) NOT NULL DEFAULT '',
  `inputer` char(30) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`payid`),
  KEY `type` (`typeid`,`year`,`month`,`date`),
  KEY `username` (`username`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_payment`;
CREATE TABLE IF NOT EXISTS `phpmps_payment` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `paycenter` varchar(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `logo` varchar(100) NOT NULL,
  `sendurl` varchar(100) NOT NULL,
  `receiveurl` varchar(100) NOT NULL,
  `partnerid` varchar(100) NOT NULL,
  `keycode` varchar(100) NOT NULL,
  `percent` float unsigned NOT NULL DEFAULT '0',
  `email` varchar(60) NOT NULL,
  `enable` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

INSERT INTO `phpmps_payment` (`id`, `paycenter`, `name`, `logo`, `sendurl`, `receiveurl`, `partnerid`, `keycode`, `percent`, `email`, `enable`) VALUES
(1, 'alipay', '支付宝', 'http://img.alipay.com/img/logo/logo_126x37.gif', 'http://www.alipay.com/cooperate/gateway.do', '','202020202020', 'abcde', '1','phpmps@qq.com', 1);

DROP TABLE IF EXISTS `phpmps_pay_exchange`;
CREATE TABLE IF NOT EXISTS `phpmps_pay_exchange` (
  `exchangeid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL DEFAULT '',
  `type` enum('money','gold','credit') NOT NULL DEFAULT 'money',
  `value` float NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL,
  `addtime` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`exchangeid`),
  KEY `username` (`username`,`type`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_pay_online`;
CREATE TABLE IF NOT EXISTS `phpmps_pay_online` (
  `payid` int(11) NOT NULL AUTO_INCREMENT,
  `paycenter` varchar(50) NOT NULL DEFAULT '',
  `username` varchar(30) NOT NULL DEFAULT '',
  `orderid` varchar(64) NOT NULL DEFAULT '',
  `moneytype` varchar(10) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `trade_fee` double NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `contactname` varchar(50) NOT NULL DEFAULT '',
  `telephone` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `sendtime` int(11) NOT NULL DEFAULT '0',
  `receivetime` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`payid`),
  KEY `username` (`username`,`orderid`,`status`),
  KEY `orderid` (`orderid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_report`;
CREATE TABLE `phpmps_report` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `info` int(11) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `postdate` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ip` (`ip`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `phpmps_type`;
CREATE TABLE IF NOT EXISTS `phpmps_type` (
  `typeid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `typename` varchar(32) NOT NULL,
  `listorder` smallint(5) NOT NULL,
  `keywords` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `module` char(10) NOT NULL,
  PRIMARY KEY (`typeid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

INSERT INTO `phpmps_type` (`typeid`, `typename`, `listorder`, `keywords`, `description`, `module`) VALUES
(1, '注册与登陆', 1, '注册与登陆', '注册与登陆', 'help'),
(2, '信息相关', 2, '信息相关', '信息相关', 'help'),
(3, '积分与信息币相关', 3, '积分与信息币相关', '积分与信息币相关', 'help');

DROP TABLE IF EXISTS `phpmps_ver`;
CREATE TABLE IF NOT EXISTS `phpmps_ver` (
  `vid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` varchar(50) NOT NULL,
  PRIMARY KEY (`vid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

INSERT INTO `phpmps_ver` (`vid`, `question`, `answer`) VALUES
(1, '35+47=', '82'),
(2, '72-38=', '34'),
(3, '57X4=', '228');