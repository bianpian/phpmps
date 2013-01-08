<?php

define('IN_PHPMPS', true);
require_once dirname(__FILE__) . '/include/common.php';

chkadmin('flash');

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$sql = "SELECT * FROM {$table}flash ORDER BY flaorder,id";
		$res = $db->query($sql);
		$flash = array();
		while($row = $db->fetchRow($res)) {
			$flash[]      = $row;
		}
		$action = array('name'=>'添加flash', 'href'=>'flash.php?act=add');
		include tpl('list_flash');
	break;

	case 'add':
		$maxorder = $db->getOne("SELECT MAX(flaorder) FROM {$table}flash");
		$maxorder = $maxorder + 1;
		$action = array('name'=>'flash列表', 'href'=>'flash.php?act=list');
		include tpl('add_flash');
	break;

	case 'insert':
		if(empty($_REQUEST['url']))show('链接不能为空');
		if(empty($_FILES['file']['name']))
        {
			show('没有上传图片');
		}
		else
		{
            $name = date('Ymd');
            for($i = 0;$i < 6;$i++) {
                $name .= chr(mt_rand(97, 122));
            }
            $name .= '.' . end(explode('.', $_FILES['file']['name']));
            $to    = PHPMPS_ROOT . 'data/flashimage/' . $name;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $to)){
                $image = "data/flashimage/" . $name;
            }
        }
		$url = trim($_REQUEST['url']);
		$flaorder = intval($_REQUEST['order']);
		$sql = "INSERT INTO {$table}flash (image,url,flaorder) VALUES ('$image','$url','$flaorder');";
		$res = $db->query($sql);
		$res ? $msg = "添加成功" : $msg = "添加失败";
		clear_caches('phpcache', 'flash');

		admin_log("添加flash $name 成功");
		show($msg, 'flash.php?act=add');
	break;
	
	case 'edit':
		$id  = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}flash WHERE id = '$id' ";
		$flash = $db->getRow($sql);
		include tpl('edit_flash');
	break;

	case 'update':
		if(empty($_REQUEST['url']))show('链接不能为空');
		if(empty($_FILES['file']['name']) && empty($_REQUEST['fileurl'])) {
			show('没有上传图片');
		}
		if(!empty($_FILES['file']['name'])) {
			if($_REQUEST['fileurl'] != '' && is_file('../'.$_REQUEST['fileurl'])) {
				@unlink('../'.$_REQUEST['fileurl']);
			}
            $name = date('Ymd');
            for($i = 0;$i < 6;$i++) {
                $name .= chr(mt_rand(97, 122));
            }
            $name .= '.' . end(explode('.', $_FILES['file']['name']));
            $to    = PHPMPS_ROOT . 'data/flashimage/' . $name;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $to)){
                $image = "data/flashimage/" . $name;
            }
        }else{
			$image = $_REQUEST['fileurl'];
		}
		$url = trim($_REQUEST['url']);
		$flaorder = intval($_REQUEST['order']);
		$id  = intval($_REQUEST['id']);
		$sql = "UPDATE {$table}flash SET image='$image',url='$url',flaorder='$flaorder' WHERE id = '$id' ";
		$res = $db->query($sql);
		$res ? $msg = "修改成功" : $msg = "修改失败";
		clear_caches('phpcache', 'flash');

		admin_log("flash $image $msg");
		show($msg, 'flash.php?act=list');
	break;

	case 'delete':
		$id = intval($_REQUEST['id']);
		if(empty($id))show('没有选择记录');
		$sql = "SELECT image FROM {$table}flash WHERE id='$id' ";
		$image = $db->getOne($sql);
		if($image != '' && is_file(PHPMPS_ROOT.$image)) {
			@unlink(PHPMPS_ROOT.$image);
		}
		$sql = "DELETE FROM {$table}flash WHERE id='$id'";
	    $res = $db->query($sql);
		$res ? $msg = "修改成功" : $msg = "修改失败";
		clear_caches('phpcache', 'flash');
		admin_log("flash $name $msg");
		show('删除flash成功', 'flash.php?act=list');
	break;
}
?>