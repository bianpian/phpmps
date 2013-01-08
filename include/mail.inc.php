<?php
if(!defined('IN_PHPMPS'))
{
	die('Access Denied');
}

if($CFG['sendmailtype'] == 'mail')
{
	function sendmail($mail_to, $mail_subject, $mail_body, $mail_from = '')
	{
		global $CFG;
		$mail_from = $mail_from ? $mail_from : $CFG['webname']." <".$CFG['smtpuser'].">";
		$mail_subject = str_replace("\r", '', str_replace("\n", '', $mail_subject));
		$mail_body = str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $mail_body)))));
		$mail_body = str_replace("\r", '', $mail_body);

		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=".$charset."\r\n";
		$headers .= "From: $mail_from\r\n";

		return strpos($mail_to, ',') ? @mail($mail_from, $mail_subject, $mail_body, $headers."Bcc: $mail_to") :	@mail($mail_to, $mail_subject, $mail_body, $headers);
	}
}
elseif($CFG['smtpuser'])
{
	require_once PHPMPS_ROOT.'include/smtp.class.php';

	$smtp = new smtp($CFG['smtphost'], $CFG['smtpport']?$CFG['smtpport']:'25', TRUE, $CFG['smtpuser'], $CFG['smtppass']);
	$smtp->debug = FALSE;

	function sendmail($mail_to, $mail_subject, $mail_body, $mail_from = '')
	{
		global $smtp,$CFG,$charset;
		$mail_to = trim($mail_to);
		$mail_from = $mail_from ? $mail_from : $CFG['webname']." &lt;".$CFG['smtpuser']."&gt;";
		$mail_subject = str_replace("\r", '', str_replace("\n", '', $mail_subject));
		if(strtolower($charset)=='utf-8')$mail_subject = "=?UTF-8?B?" . base64_encode($mail_subject) . "?=";

		$mail_body = str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $mail_body)))));
		$mail_from = "<".$CFG['smtpuser'].">";

		return strpos($mail_to, ',') ? $smtp->sendmail($CFG['smtpuser'],$mail_from,$mail_subject,$mail_body,'HTML',$mail_to) : $smtp->sendmail($mail_to,$mail_from,$mail_subject,$mail_body,"HTML");
	}
}
?>