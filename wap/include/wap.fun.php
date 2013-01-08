<?php


function tpl($file)
{
	$file = PHPMPS_ROOT.'wap/templates/'.$file.'.htm';
    return $file;
}

function encode_output($str)
{
	global $charset;

    if ($charset != 'utf-8')
    {
        $str = iconvs($charset, 'utf-8', $str);
    }
    return strip_tags($str);
}





?>