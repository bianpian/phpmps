<?php
defined('IN_PHPMPS') or exit('Access Denied');
require_once PHPMPS_ROOT.'include/payonline/'.$paycenter.'/alipay_notify.php';
require_once PHPMPS_ROOT.'include/payonline/'.$paycenter.'/alipay_config.php';

//接收返回的参数
$pay['notify_type']=$notify_type;			//交易类型
$pay['notify_id']=$notify_id;				//支付宝流水号
$pay['notify_time']=$notify_time;			//通知时间
$pay['sign']=$sign; 						//加密字符串
$pay['sign_type']=$sing_type;				//加密方式
$pay['out_trade_no']=$out_trade_no;			//交易ID号
$pay['subject']=$subject;					//商品名称
$pay['body']=$body;							//商品描述
$pay['total_fee']=$total_fee;				//成交价
$pay['payment_type']=$payment_type;			//支付类型
$pay['seller_email']=$seller_email;			//卖家EMIAL
$pay['seller_id']=$seller_id ;				//卖家ID
$pay['buyer_id']=$buyer_id;					//买家ID
$pay['buyer_email']=$buyer_email;			//卖家EMAIL
$pay['trade_status']=$trade_status;			//交易状态
$pay['exterface'] = 'create_direct_pay_by_user';
$pay['is_success']=$is_success;
$pay['trade_no']=$trade_no;

$alipay = new alipay_notify($_GET,$security_code,$sign_type,$_input_charset,$transport);//对URL组合
$verify_result = $alipay->return_verify();//MD5验证

if($verify_result)
{
	
	$info = dopay($out_trade_no, $total_fee,'');
    if($info)
	{
		$paystatus = 1;
		extract($info, EXTR_OVERWRITE);
	}
	else
	{
		$paystatus = 2;
	}
}
else
{
	$paystatus = 0;
}
?>