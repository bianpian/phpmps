<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

$_SESSION['adminid']  = '';
$_SESSION['adminname'] = '';
show('退出成功', 'index.php');
?>
