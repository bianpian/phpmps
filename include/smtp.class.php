<?php
if(!defined('IN_PHPMPS')) {
	die('Access Denied');
}

/**
* 邮件发送类，可通过smtp服务器发送邮件
*/
class smtp
{
	/**
	* smtp服务器，例如 smtp.163.com
	* @var string
	*/
	var $smtp_host;

	/**
	* smtp服务器端口，默认为25
	* @var string
	*/
	var $smtp_port = 25;

	/**
	* smtp服务器是否需要认证
	* @var bool
	*/
	var $auth = TRUE;

	/**
	* 邮件帐号，例如 phpcms@163.com
	* @var string
	*/
	var $user;

	/**
	* 邮件密码
	* @var string
	*/
	var $pass;

	/**
	* 是否显示调试信息
	* @var bool
	*/
	var $debug = FALSE;

	/**
	* @access private
	*/
	var $time_out;
	/**
	* @access private
	*/
	var $host_name;
	/**
	* @access private
	*/
	var $log_file;
	/**
	* @access private
	*/
	var $sock;

    /**
	* 构造函数，连接smtp服务器
	* @param string smtp服务器
	* @param int smtp服务器端口
	* @param bool smtp服务器是否需要认证
	* @param string smtp服务器邮件帐号
	* @param string smtp服务器邮件密码
	*/
	function smtp($smtp_host, $smtp_port = 25, $auth = TRUE, $user='', $pass='')
	{
		$this->debug = FALSE;
		$this->smtp_port = trim($smtp_port);
		$this->smtp_host = trim($smtp_host);
		$this->time_out = 30; //is used in fsockopen()
		$this->auth = trim($auth);//auth
		$this->user = trim($user);
		$this->pass = trim($pass);
		$this->host_name = "localhost"; //is used in HELO command
		$this->log_file = '';
		$this->sock = FALSE;
	}

    /**
	* 发送邮件的主函数
	* @param string 收件人
	* @param string 发件人
	* @param string 邮件主题
	* @param string 邮件正文
	* @param string 邮件类型，可选项为 HTML 、TEXT
	* @param string 抄送邮件
	* @param string 暗送邮件
	* @param string 附件
	*/
	function sendmail($to, $from, $subject = '', $body = '', $mailtype = "HTML", $cc = '', $bcc = '', $additional_headers = '')
	{
		global $CFG,$charset;
		$to = trim($to);
		$mail_from = $CFG['smtpuser'];
		$body = ereg_replace("(^|(\r\n))(\.)", "\1.\3", $body);
		$header = "MIME-Version:1.0\r\n";
		if(strtoupper($mailtype) == "HTML")
		{
			$header .= "Content-Type:text/html;charset=".$charset."\r\n";
		}
		$header .= "To: ".$to."\r\n";
		if ($cc != '')
		{
			$header .= "Cc: ".$cc."\r\n";
		}
		$header .= "From: ".$this->get_mailfrom($from, $mail_from)."\r\n";
		$header .= "Subject: ".$subject."\r\n";
		$header .= $additional_headers;
		$header .= "Date: ".date("r")."\r\n";
		$header .= "X-Mailer:By Apache (PHP/".phpversion().")\r\n";
		list($msec, $sec) = explode(' ', microtime());
		$header .= "Message-ID: <".date('YmdHis', $sec).".".($msec*1000000).".".substr($mail_from,strpos($mail_from,'@')).">\r\n";
		$TO = explode(',', $this->strip_comment($to));
		if ($cc != '')
		{
			$TO = array_merge($TO, explode(',', $this->strip_comment($cc)));
		}
		if ($bcc != '')
		{
			$TO = array_merge($TO, explode(',', $this->strip_comment($bcc)));
		}
		$sent = TRUE;
		foreach ($TO as $rcpt_to)
		{
			$rcpt_to = $this->get_address($rcpt_to);
			if (!$this->smtp_sockopen($rcpt_to))
			{
				$this->log_write("Error: Cannot send email to ".$rcpt_to."\n");
				$sent = FALSE;
				continue;
			}
			if ($this->smtp_send($this->host_name, $mail_from, $rcpt_to, $header, $body))
			{
				$this->log_write("E-mail has been sent to <".$rcpt_to.">\n");
			}
			else
			{
				$this->log_write("Error: Cannot send email to <".$rcpt_to.">\n");
				$sent = FALSE;
			}
			fclose($this->sock);
			$this->log_write("Disconnected from remote host\n");
		}
		return $sent;
	}

	/**
	* @access private
	*/
	function smtp_send($helo, $from, $to, $header, $body = '')
	{
		if(!$this->smtp_putcmd("HELO", $helo))
		{
			return $this->smtp_error("sending HELO command");
		}
		#auth
		if($this->auth)
		{
			if (!$this->smtp_putcmd("AUTH LOGIN", base64_encode($this->user)))
			{
				return $this->smtp_error("sending HELO command");
			}
			if (!$this->smtp_putcmd('', base64_encode($this->pass)))
			{
				return $this->smtp_error("sending HELO command");
			}
		}
		if (!$this->smtp_putcmd("MAIL", "FROM:<".$from.">"))
		{
			return $this->smtp_error("sending MAIL FROM command");
		}
		if (!$this->smtp_putcmd("RCPT", "TO:<".$to.">"))
		{
			return $this->smtp_error("sending RCPT TO command");
		}
		if (!$this->smtp_putcmd("DATA"))
		{
			return $this->smtp_error("sending DATA command");
		}
		if (!$this->smtp_message($header, $body))
		{
			return $this->smtp_error("sending message");
		}
		if (!$this->smtp_eom())
		{
			return $this->smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
		}
		if (!$this->smtp_putcmd("QUIT"))
		{
			return $this->smtp_error("sending QUIT command");
		}
		return TRUE;
	}

	/**
	* @access private
	*/
	function smtp_sockopen($address)
	{
		if ($this->smtp_host == '')
		{
			return $this->smtp_sockopen_mx($address);
		}
		else
		{
			return $this->smtp_sockopen_relay();
		}
	}

	/**
	* @access private
	*/
	function smtp_sockopen_relay()
	{
		$this->log_write("Trying to ".$this->smtp_host.":".$this->smtp_port."\n");
		$this->sock = @fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, $this->time_out);
		if (!($this->sock && $this->smtp_ok()))
		{
			$this->log_write("Error: Cannot connenct to relay host ".$this->smtp_host."\n");
			$this->log_write("Error: ".$errstr." (".$errno.")\n");
			return FALSE;
		}
		$this->log_write("Connected to relay host ".$this->smtp_host."\n");
		return TRUE;
	}

	/**
	* @access private
	*/
	function smtp_sockopen_mx($address)
	{
		$domain = ereg_replace("^.+@([^@]+)$", "\1", $address);
		if (!@getmxrr($domain, $MXHOSTS))
		{
			$this->log_write("Error: Cannot resolve MX \"".$domain."\"\n");
			return FALSE;
		}
		foreach ($MXHOSTS as $host)
		{
			$this->log_write("Trying to ".$host.":".$this->smtp_port."\n");
			$this->sock = @fsockopen($host, $this->smtp_port, $errno, $errstr, $this->time_out);
			if (!($this->sock && $this->smtp_ok()))
			{
				$this->log_write("Warning: Cannot connect to mx host ".$host."\n");
				$this->log_write("Error: ".$errstr." (".$errno.")\n");
				continue;
			}
			$this->log_write("Connected to mx host ".$host."\n");
			return TRUE;
		}
		$this->log_write("Error: Cannot connect to any mx hosts (".implode(", ", $MXHOSTS).")\n");
		return FALSE;
	}

	/**
	* @access private
	*/
	function smtp_message($header, $body)
	{
		fputs($this->sock, $header."\r\n".$body);
		$this->smtp_debug("> ".str_replace("\r\n", "\n"."> ", $header."\n> ".$body."\n> "));
		return TRUE;
	}

	/**
	* @access private
	*/
	function smtp_eom()
	{
		fputs($this->sock, "\r\n.\r\n");
		$this->smtp_debug(". [EOM]\n");
		return $this->smtp_ok();
	}

	/**
	* @access private
	*/
	function smtp_ok()
	{
		$response = str_replace("\r\n", '', fgets($this->sock, 512));
		$this->smtp_debug($response."\n");
		if (!ereg("^[23]", $response))
		{
			fputs($this->sock, "QUIT\r\n");
			fgets($this->sock, 512);
			$this->log_write("Error: Remote host returned \"".$response."\"\n");
			return FALSE;
		}
		return TRUE;
	}

	/**
	* @access private
	*/
	function smtp_putcmd($cmd, $arg = '')
	{
		if ($arg != '')
		{
			if($cmd=='')
				$cmd = $arg;
			else 
				$cmd = $cmd." ".$arg;
		}
		fputs($this->sock, $cmd."\r\n");
		$this->smtp_debug("> ".$cmd."\n");
		return $this->smtp_ok();
	}

	/**
	* @access private
	*/
	function smtp_error($string)
	{
		$this->log_write("Error: Error occurred while ".$string.".\n");
		return FALSE;
	}

	/**
	* @access private
	*/
	function log_write($message)
	{
		$this->smtp_debug($message);
		if ($this->log_file == '')
		{
			return TRUE;
		}
		$message = date("M d H:i:s ").get_current_user()."[".getmypid()."]: ".$message;
		if (!@file_exists($this->log_file) || !($fp = @fopen($this->log_file, "a")))
		{
			$this->smtp_debug("Warning: Cannot open log file \"".$this->log_file."\"\n");
			return FALSE;
		}
		flock($fp, LOCK_EX);
		fputs($fp, $message);
		fclose($fp);
		return TRUE;
	}

	/**
	* @access private
	*/
	function strip_comment($address)
	{
		$comment = "\([^()]*\)";
		while (ereg($comment, $address))
		{
			$address = ereg_replace($comment, '', $address);
		}
		return $address;
	}

	/**
	* @access private
	*/
	function get_address($address)
	{
		return trim(preg_replace("/(.*[<])?([^<>]+)[>]?/i", "$2", $address));
	}

	/**
	* @access private
	*/
	function get_mailfrom($address, $mail_from)
	{
		return strpos($address, '@') ? trim(preg_replace("/^([^<]*?)<([^>]+)>$/i", "$1<".$mail_from.">", $address)) : $address."<$mail_from>";
	}

	/**
	* @access private
	*/
	function smtp_debug($message)
	{
		if ($this->debug)
		{
			echo $message;
		}
	}
}
?>