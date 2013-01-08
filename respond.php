<?php
define('IN_PHPMPS', true);
require dirname(__FILE__) . '/include/common.php';
require PHPMPS_ROOT . 'include/pay.fun.php';

extract($_REQUEST);
$payonline_setting = get_pay_setting();
$paycenter = 'alipay';
array_key_exists($paycenter, $payonline_setting) or showmsg('支付错误');
@extract($payonline_setting[$paycenter]);
require PHPMPS_ROOT.'include/payonline/'.$paycenter.'/receive.php';

$total_amount = $amount + $trade_fee;
$seo['title'] = '支付返回信息';
include template('payreceive');
?>