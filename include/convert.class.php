<?php

if (!defined('IN_PHPMPS'))
{
    die('Access Denied');
}

class Chinese
{
    var $PINYIN_table = array();
    var $unicode_table = array();
    var $ctf;
    var $SourceText = "";
    var $config  =  array(
        'codetable_dir'         => 'include/table/',
        'source_lang'           => '', 
        'target_lang'           => '', 
        'GBtoBIG5_table'        => 'gb-big5.table',
        'BIG5toGB_table'        => 'big5-gb.table',
        'GBtoPINYIN_table'      => 'gb-pinyin.table',
        'GBtoUnicode_table'     => 'gb-unicode.table',
        'BIG5toUnicode_table'   => 'big5-unicode.table'
    );
    function Chinese()
    {
    }

    function Convert( $source_lang , $target_lang , $source_string='' )
    {
        if ($source_lang == $target_lang || $source_string == '')
        {
            return $source_string;
        }
        
        if ($source_lang != '') {
            $this->config['source_lang'] = $source_lang;
        }

        if ($target_lang != '') {
            $this->config['target_lang'] = $target_lang;
        }

        $this->SourceText = $source_string;
        
        $this->OpenTable();
        if ( ($this->config['source_lang']=="GB2312" || $this->config['source_lang']=="BIG5") && ($this->config['target_lang']=="GB2312" || $this->config['target_lang']=="BIG5") ) {
            return $this->GB2312toBIG5();
        }
        if ( ($this->config['source_lang']=="GB2312" || $this->config['source_lang']=="BIG5") && $this->config['target_lang']=="PINYIN" ) {
            return $this->CHStoPINYIN();
        }
        if ( ($this->config['source_lang']=="GB2312" || $this->config['source_lang']=="BIG5" || $this->config['source_lang']=="UTF8") && ($this->config['target_lang']=="UTF8" || $this->config['target_lang']=="GB2312" || $this->config['target_lang']=="BIG5") ) {
            return $this->CHStoUTF8();
        }
        if ( ($this->config['source_lang']=="GB2312" || $this->config['source_lang']=="BIG5") && $this->config['target_lang']=="UNICODE" ) {
            return $this->CHStoUNICODE();
        }
    }
    function _hex2bin( $hexdata )
    {
        $bindata = '';
        for ( $i=0; $i<strlen($hexdata); $i+=2 )
        $bindata.=chr(hexdec(substr($hexdata,$i,2)));

        return $bindata;
    }
    function OpenTable()
    {
        if ($this->config['source_lang']=="GB2312") {
            if ($this->config['target_lang'] == "BIG5") {
                $this->ctf = fopen($this->config['codetable_dir'].$this->config['GBtoBIG5_table'], "r");
                if (is_null($this->ctf)) {
                    echo 'Fail to open coverting table!';
                    exit;
                }
            }

            if ($this->config['target_lang'] == "PINYIN") {
                $tmp = @file($this->config['codetable_dir'].$this->config['GBtoPINYIN_table']);
                if (!$tmp) {
                    echo 'Fail to open coverting table!';
                    exit;
                }
                $i = 0;
                for ($i=0; $i<count($tmp); $i++) {
                    $tmp1 = explode("	", $tmp[$i]);
                    $this->PINYIN_table[$i]=array($tmp1[0],$tmp1[1]);
                }
            }

            if ($this->config['target_lang'] == "UTF8") {
                $tmp = @file($this->config['codetable_dir'].$this->config['GBtoUnicode_table']);
                if (!$tmp) {
                    echo 'Fail to convert encoding!';
                    exit;
                }
                $this->unicode_table = array();
                while(list($key,$value)=each($tmp))
                $this->unicode_table[hexdec(substr($value,0,6))]=substr($value,7,6);
            }

            if ($this->config['target_lang'] == "UNICODE") {
                $tmp = @file($this->config['codetable_dir'].$this->config['GBtoUnicode_table']);
                if (!$tmp) {
                    echo 'Fail to open coverting table!';
                    exit;
                }
                $this->unicode_table = array();
                while(list($key,$value)=each($tmp))
                $this->unicode_table[hexdec(substr($value,0,6))]=substr($value,9,4);
            }
        }

        if ($this->config['source_lang']=="BIG5") {
            if ($this->config['target_lang'] == "GB2312") {
                $this->ctf = fopen($this->config['codetable_dir'].$this->config['BIG5toGB_table'], "r");
                if (is_null($this->ctf)) {
                    echo 'Fail to open coverting table!';
                    exit;
                }
            }
            if ($this->config['target_lang'] == "UTF8") {
                $tmp = @file($this->config['codetable_dir'].$this->config['BIG5toUnicode_table']);
                if (!$tmp) {
                    echo 'Fail to open coverting table!';
                    exit;
                }
                $this->unicode_table = array();
                while(list($key,$value)=each($tmp))
                $this->unicode_table[hexdec(substr($value,0,6))]=substr($value,7,6);
            }

            if ($this->config['target_lang'] == "UNICODE") {
                $tmp = @file($this->config['codetable_dir'].$this->config['BIG5toUnicode_table']);
                if (!$tmp) {
                    echo 'Fail to open coverting table!';
                    exit;
                }
                $this->unicode_table = array();
                while(list($key,$value)=each($tmp))
                $this->unicode_table[hexdec(substr($value,0,6))]=substr($value,9,4);
            }

            if ($this->config['target_lang'] == "PINYIN") {
                $tmp = @file($this->config['codetable_dir'].$this->config['GBtoPINYIN_table']);
                if (!$tmp) {
                    echo 'Fail to open coverting table!';
                    exit;
                }
                $i = 0;
                for ($i=0; $i<count($tmp); $i++) {
                    $tmp1 = explode("	", $tmp[$i]);
                    $this->PINYIN_table[$i]=array($tmp1[0],$tmp1[1]);
                }
            }
        }

        if ($this->config['source_lang']=="UTF8") {

            if ($this->config['target_lang'] == "GB2312") {
                $tmp = @file($this->config['codetable_dir'].$this->config['GBtoUnicode_table']);
                if (!$tmp) {
                    echo 'Fail to open coverting table!';
                    exit;
                }
                $this->unicode_table = array();
                while(list($key,$value)=each($tmp))
                $this->unicode_table[hexdec(substr($value,7,6))]=substr($value,0,6);
            }

            if ($this->config['target_lang'] == "BIG5") {
                $tmp = @file($this->config['codetable_dir'].$this->config['BIG5toUnicode_table']);
                if (!$tmp) {
                    echo 'Fail to open coverting table!';
                    exit;
                }
                $this->unicode_table = array();
                while(list($key,$value)=each($tmp))
                $this->unicode_table[hexdec(substr($value,7,6))]=substr($value,0,6);
            }
        }

    } 
    function OpenFile( $position , $isHTML=false )
    {
        $tempcontent = @file($position);

        if (!$tempcontent) {
            echo 'Fail to open file!';
            exit;
        }

        $this->SourceText = implode("",$tempcontent);

        if ($isHTML) {
            $this->SourceText = eregi_replace( "charset=".$this->config['source_lang'] , "charset=".$this->config['target_lang'] , $this->SourceText);

            $this->SourceText = eregi_replace("\n", "", $this->SourceText);

            $this->SourceText = eregi_replace("\r", "", $this->SourceText);
        }
    } 
    function SiteOpen( $position )
    {
        $tempcontent = @file($position);

        if (!$tempcontent) {
            echo 'Fail to open file!';
            exit;
        }

        $this->SourceText = implode("",$tempcontent);

        $this->SourceText = eregi_replace( "charset=".$this->config['source_lang'] , "charset=".$this->config['target_lang'] , $this->SourceText);


    }
    function setvar( $parameter , $value )
    {
        if(!trim($parameter))
        return $parameter;

        $this->config[$parameter] = $value;

    }
    function CHSUtoUTF8($c)
    {
        $str="";

        if ($c < 0x80) {
            $str.=$c;
        }

        else if ($c < 0x800) {
            $str.=(0xC0 | $c>>6);
            $str.=(0x80 | $c & 0x3F);
        }

        else if ($c < 0x10000) {
            $str.=(0xE0 | $c>>12);
            $str.=(0x80 | $c>>6 & 0x3F);
            $str.=(0x80 | $c & 0x3F);
        }

        else if ($c < 0x200000) {
            $str.=(0xF0 | $c>>18);
            $str.=(0x80 | $c>>12 & 0x3F);
            $str.=(0x80 | $c>>6 & 0x3F);
            $str.=(0x80 | $c & 0x3F);
        }

        return $str;
    }
    function CHStoUTF8(){

        if ($this->config["source_lang"]=="BIG5" || $this->config["source_lang"]=="GB2312") {
            $ret="";

            while($this->SourceText){

                if(ord(substr($this->SourceText,0,1))>127){

                    if ($this->config["source_lang"]=="BIG5") {
                        $utf8=$this->CHSUtoUTF8(hexdec($this->unicode_table[hexdec(bin2hex(substr($this->SourceText,0,2)))]));
                    }
                    if ($this->config["source_lang"]=="GB2312") {
                        $utf8=$this->CHSUtoUTF8(hexdec($this->unicode_table[hexdec(bin2hex(substr($this->SourceText,0,2)))-0x8080]));
                    }
                    for($i=0;$i<strlen($utf8);$i+=3)
                    $ret.=chr(substr($utf8,$i,3));

                    $this->SourceText=substr($this->SourceText,2,strlen($this->SourceText));
                }

                else{
                    $ret.=substr($this->SourceText,0,1);
                    $this->SourceText=substr($this->SourceText,1,strlen($this->SourceText));
                }
            }
            $this->unicode_table = array();
            $this->SourceText = "";
            return $ret;
        }

        if ($this->config["source_lang"]=="UTF8") {
            $out = "";
            $len = strlen($this->SourceText);
            $i = 0;
            while($i < $len) {
                $c = ord( substr( $this->SourceText, $i++, 1 ) );
                switch($c >> 4)
                {
                    case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
                        // 0xxxxxxx
                        $out .= substr( $this->SourceText, $i-1, 1 );
                        break;
                    case 12: case 13:
                        // 110x xxxx   10xx xxxx
                        $char2 = ord( substr( $this->SourceText, $i++, 1 ) );
                        $char3 = $this->unicode_table[(($c & 0x1F) << 6) | ($char2 & 0x3F)];

                        if ($this->config["target_lang"]=="GB2312")
                        $out .= $this->_hex2bin( dechex(  $char3 + 0x8080 ) );

                        if ($this->config["target_lang"]=="BIG5")
                        $out .= $this->_hex2bin( $char3 );
                        break;
                    case 14:
                        // 1110 xxxx  10xx xxxx  10xx xxxx
                        $char2 = ord( substr( $this->SourceText, $i++, 1 ) );
                        $char3 = ord( substr( $this->SourceText, $i++, 1 ) );
                        $char4 = $this->unicode_table[(($c & 0x0F) << 12) | (($char2 & 0x3F) << 6) | (($char3 & 0x3F) << 0)];

                        if ($this->config["target_lang"]=="GB2312")
                        $out .= $this->_hex2bin( dechex ( $char4 + 0x8080 ) );

                        if ($this->config["target_lang"]=="BIG5")
                        $out .= $this->_hex2bin( $char4 );
                        break;
                }
            }

            return $out;
        }
    } 
    function CHStoUNICODE()
    {

        $utf="";

        while($this->SourceText)
        {
            if (ord(substr($this->SourceText,0,1))>127)
            {

                if ($this->config["source_lang"]=="GB2312")
                $utf.="&#x".$this->unicode_table[hexdec(bin2hex(substr($this->SourceText,0,2)))-0x8080].";";

                if ($this->config["source_lang"]=="BIG5")
                $utf.="&#x".$this->unicode_table[hexdec(bin2hex(substr($this->SourceText,0,2)))].";";

                $this->SourceText=substr($this->SourceText,2,strlen($this->SourceText));
            }
            else
            {
                $utf.=substr($this->SourceText,0,1);
                $this->SourceText=substr($this->SourceText,1,strlen($this->SourceText));
            }
        }
        return $utf;
    }
    function GB2312toBIG5()
    {
        $max=strlen($this->SourceText)-1;

        for($i=0;$i<$max;$i++){

            $h=ord($this->SourceText[$i]);

            if($h>=160){

                $l=ord($this->SourceText[$i+1]);

                if($h==161 && $l==64){
                    $gb="  ";
                }
                else{
                    fseek($this->ctf,($h-160)*510+($l-1)*2);
                    $gb=fread($this->ctf,2);
                }

                $this->SourceText[$i]=$gb[0];
                $this->SourceText[$i+1]=$gb[1];
                $i++;
            }
        }
        fclose($this->ctf);

        $result = $this->SourceText;

        $this->SourceText = "";

        return $result;
    } 
    function PINYINSearch($num){

        if($num>0&&$num<160){
            return chr($num);
        }

        elseif($num<-20319||$num>-10247){
            return "";
        }

        else{

            for($i=count($this->PINYIN_table)-1;$i>=0;$i--){
                if($this->PINYIN_table[$i][1]<=$num)
                break;
            }

            return $this->PINYIN_table[$i][0];
        }
    }
    function CHStoPINYIN(){
        if ( $this->config['source_lang']=="BIG5" ) {
            $this->ctf = fopen($this->config['codetable_dir'].$this->config['BIG5toGB_table'], "r");
            if (is_null($this->ctf)) {
                echo 'Fail to open file!';
                exit;
            }

            $this->SourceText = $this->GB2312toBIG5();
            $this->config['target_lang'] = "PINYIN";
        }

        $ret = array();
        $ri = 0;
        for($i=0;$i<strlen($this->SourceText);$i++){

            $p=ord(substr($this->SourceText,$i,1));

            if($p>160){
                $q=ord(substr($this->SourceText,++$i,1));
                $p=$p*256+$q-65536;
            }

            $ret[$ri]=$this->PINYINSearch($p);
            $ri = $ri + 1;
        }
        $this->SourceText = "";

        $this->PINYIN_table = array();

        return implode(" ", $ret);
    }

  }


?>
