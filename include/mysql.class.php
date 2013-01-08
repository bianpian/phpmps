<?php

if (!defined('IN_PHPMPS')) {
    die('Access Denied');
}

class mysql
{
    var $link    = NULL;
	var $settings   = array();
    var $queryCount = 0;
    var $queryTime  = '';
    var $queryLog   = array();
    var $root_path      = '';
    var $error_message  = array();
    var $version        = '';
    var $dbhash         = '';
    var $starttime      = 0;
    var $timeline       = 0;
    var $timezone       = 0;

    function __construct($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $quiet = 0)
    {
        $this->mysql($dbhost, $dbuser, $dbpw, $dbname, $pconnect, $quiet);
    }

    function mysql($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $quiet = 0)
    {
		global $charset;

        if (defined('PHPMPS_ROOT') && !$this->root_path) {
            $this->root_path = PHPMPS_ROOT;
        }
        if ($quiet) {
            $this->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, $quiet);
        } else {
            $this->settings = array('dbhost'=> $dbhost,'dbuser'=> $dbuser,'dbpw'=> $dbpw,'dbname'=>$dbname,'charset'=> $charset,'pconnect' => $pconnect);
        }
    }

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $quiet = 0)
    {
		global $dbcharset;

        if ($pconnect) {
            if (!($this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw))) {
                if (!$quiet) {
                    $this->ErrorMsg("Can't Pconnect MySQL Server($dbhost)!<br><a href='http://bbs.phpmps.com'>Click Here</a>");
                }
                return false;
            }
        } else {
            if (PHP_VERSION >= '4.2') {
                $this->link = @mysql_connect($dbhost, $dbuser, $dbpw, true);
            } else {
                $this->link = @mysql_connect($dbhost, $dbuser, $dbpw);
                mt_srand((double)microtime() * 1000000); 
            }

            if (!$this->link) {
                if (!$quiet) {
                    $this->ErrorMsg("Can't Connect MySQL Server($dbhost)!");
                }
                return false;
            }
        }
        $this->dbhash  = md5($this->root_path . $dbhost . $dbuser . $dbpw . $dbname);
		$this->version = mysql_get_server_info($this->link);
        
		if ($this->version > '4.1') {
            if ($dbcharset != 'latin1')  {
                mysql_query("SET character_set_connection=$dbcharset, character_set_results=$dbcharset, character_set_client=binary", $this->link);
            }
            if ($this->version > '5.0.1') {
                mysql_query("SET sql_mode=''", $this->link);
            }
        }
        $this->starttime = time();
        if ($dbname) {
            if (mysql_select_db($dbname, $this->link) === false ) {
                if (!$quiet) {
                    $this->ErrorMsg("Can't Select MySQL database($dbname)!");
                }
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    function query($sql, $type = '')
    {
		/* 如果查询的时候mysql断开链接，那么尝试连接mysql，然后处理查询*/
        if ($this->link === NULL) {
            $this->connect($this->settings['dbhost'],$this->settings['dbuser'],$this->settings['dbpw'],$this->settings['dbname'],$this->settings['charset'],$this->settings['pconnect']);
            $this->settings = array();
        }
        if ($this->queryCount++ <= 99) {
            $this->queryLog[] = $sql;
        }
        if ($this->queryTime == '') {
            if (PHP_VERSION >= '5.0.0') {
                $this->queryTime = microtime(true);
            } else {
                $this->queryTime = microtime();
            }
        }

        if (!($query = mysql_query($sql, $this->link)) && $type != 'SILENT') {
            $this->error_message[]['message'] = 'MySQL Query Error';
            $this->error_message[]['sql'] = $sql;
            $this->error_message[]['error'] = mysql_error($this->link);
            $this->error_message[]['errno'] = mysql_errno($this->link);

            $this->ErrorMsg();
            return false;
        }

        return $query;
    }

    function affected_rows()
    {
        return mysql_affected_rows($this->link);
    }
	function num_fields($query)
	{	
        return mysql_num_fields($query);
    }
    function error()
    {
        return mysql_error($this->link);
    }

    function errno()
    {
        return mysql_errno($this->link);
    }

    function num_rows($query)
    {
        return mysql_num_rows($query);
    }

    function insert_id()
    {
        return mysql_insert_id($this->link);
    }

    function fetchRow($query)
    {
        return mysql_fetch_assoc($query);
    }

	function fetcharray($query)
    {
        return mysql_fetch_array($query);
    }

    function version()
    {
        return $this->version;
    }

    function close()
    {
        return mysql_close($this->link);
    }

    function ErrorMsg($message = '', $sql = '')
    {
        if ($message) {
            echo "$message\n\n";
        } else {
            echo "<b>MySQL server error report:";
            print_r($this->error_message);
        }
        exit;
    }

    function getOne($sql, $limited = false)
    {
        if ($limited == true) {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = $this->query($sql);
        if ($res !== false) {
            $row = mysql_fetch_row($res);

            if ($row !== false) {
                return $row[0];
            } else {
                return '';
            }
        } else {
            return false;
        }
    }

    function getAll($sql)
    {
        $res = $this->query($sql);
        if ($res !== false) {
            $arr = array();
            while ($row = mysql_fetch_assoc($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else {
            return false;
        }
    }

	function getCol($sql)
    {
        $res = $this->query($sql);
        if ($res !== false) {
            $arr = array();
            while ($row = mysql_fetch_row($res)) {
                $arr[] = $row[0];
            }
            return $arr;
        } else {
            return false;
        }
    }

    function getRow($sql, $limited = false)
    {
        if ($limited == true) {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = $this->query($sql);
        if ($res !== false) {
            return mysql_fetch_assoc($res);
        } else {
            return false;
        }
    }
}
?>