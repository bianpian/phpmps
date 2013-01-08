<?php

define('IN_PHPMPS', true);
require 'include/common.php';
require '../include/com.fun.php';

$_REQUEST['act'] = $_REQUEST['act'] ? trim($_REQUEST['act']) : 'list' ;

switch ($_REQUEST['act'])
{
	case 'list':
		$cats = com_cat_options();
        $area = area_options();
		$page = empty($_REQUEST[page])? 1 : intval($_REQUEST['page']);

		$catid    = empty($_REQUEST['cat']) ? 0 : intval($_REQUEST['cat']);
		$areaid   = empty($_REQUEST['area']) ? 0 : intval($_REQUEST['area']);
		$typeid   = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
		$keywords = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		
		$where = '';
		$where = $catid > 0 ? " and catid IN (" . get_cat_children($catid) . ")": '';
		$where .= $areaid > 0 ? " and areaid IN (" . get_area_children($areaid) . ")": '';
		
		switch($typeid)
		{
			case '1':
				$where .= " and is_check=1 ";
			break;

			case '2':
				$where .= " and is_check=0 ";
			break;

			case '3':
				$where .= " and is_pro>".time();
			break;

			case '4':
				$where .= " and is_top>".time();
			break;
		}
		
		if (!empty($keywords)) {
			$where .= " AND (title LIKE '%$keywords%' OR content LIKE '%$keywords%')";
		}
		
		if(empty($where) && !isset($_POST['cat']) && !isset($_POST['area']) && !isset($_POST['keywords']) && isset($_GET['page'])) {
			$_SESSION['where'] = $_SESSION['where'];
		} elseif(empty($where) && isset($_POST)) {
			$_SESSION['where'] = '';
		} elseif(!empty($where))
		{
			$_SESSION['where'] = $where;
		}

		$sql = "SELECT COUNT(*) FROM {$table}com WHERE 1 $_SESSION[where]";
		$count = $db->getOne($sql);
		$pager = get_pager('com.php',array('act'=>'list'),$count,$page,'15'); 
		$sql = "SELECT * FROM {$table}com WHERE 1 $_SESSION[where] ORDER BY comid DESC,postdate DESC LIMIT $pager[start],$pager[size]";
		$res = $db->query($sql);
		$articles = array();
		while($row=$db->fetchRow($res)) {
			$row['comname']  = cut_str($row['comname'],'10');
			$row['postdate'] = date('Y-m-d', $row['postdate']);
			$row['is_check'] = $row['is_check']==1 ? '是' : '否';
			$articles[]      = $row;
		}
		$here = "企业列表";
		$action = array('name'=>'添加企业', 'href'=>'category.php?act=add');
	    include tpl('list_com', 'com');
	break;

	case 'edit':
		$id = intval($_REQUEST['id']);
		$sql = "SELECT * FROM {$table}com WHERE comid = '$id'";
		$info = $db->getRow($sql);
		if(empty($info)){show('信息不存在','index.php');}
		extract($info);
		$postdate = date('Y年m月d日', $postdate);
		$info_area = area_options($areaid);
		$refer = $_SERVER['HTTP_REFERER'];
		include tpl('edit_com', 'com');
	break;

	case 'update':
		$comid    = intval($_POST['id']);
		$comname  = $_POST['comname'] ? htmlspecialchars(trim($_POST['comname'])) : '';
		$areaid   = $_POST['areaid'] ? intval($_POST['areaid']) : '';
		$content  = $_POST['content'] ? htmlspecialchars(trim($_POST['content'])) : '';
		$description = cut_str($content,30);
		$linkman  = $_POST['linkman'] ? htmlspecialchars(trim($_POST['linkman'])) : '';
		$phone    = $_POST['phone'] ? trim($_POST['phone']) : '';
		$qq       = $_POST['qq'] ? intval($_POST['qq']) : '';
		$email    = $_POST['email'] ? htmlspecialchars(trim($_POST['email'])) : '';
		$address  = $_POST['address'] ? trim($_POST['address']) : '';
		$mappoint = $_POST['mappoint'] ? trim($_POST['mappoint']) : '';
		$hours     = $_POST['hours'] ? htmlspecialchars(trim($_POST['hours'])) : '';
		$introduce = cut_str($content,30);
		$fax       = trim($_POST['fax']);
		$is_check  = trim($_POST['is_check']);

		if(empty($comname))showmsg("标题不能为空");
		if(empty($content))showmsg("内容不能为空");
		if(empty($linkman))showmsg("联系人不能为空");
		if(empty($phone) && empty($qq) && empty($email))showmsg("电话、qq、email，必须填写一项");

		$sql = "UPDATE {$table}com SET
				areaid='$areaid',
				comname='$comname',
				introduce='$introduce',
				description='$description',
				linkman='$linkman',
				email='$email',
				qq='$qq',
				phone='$phone',
				mappoint='$mappoint',
				address='$address',
				fax='$fax',
				hours='$hours',
				is_check='$is_check'
				where comid = '$comid' ";
		$res = $db->query($sql);

		admin_log("编辑信息 $comid 成功");
		$refer = trim($_POST['refer']);
		show('修改成功', $refer);
	break;

	case 'batch':
		$id = is_array($_REQUEST['id']) ? join(',', $_REQUEST['id']) : intval($_REQUEST['id']);
		if(empty($id))show('没有选择记录');
		if(empty($_REQUEST['type']))show('没有选择操作');
		switch ($_REQUEST['type'])
		{
			case 'delete':
				//删除图片
				$sql = "SELECT thumb FROM {$table}com WHERE comid IN ($id)";
				$image = $db->getAll($sql);
				foreach((array)$image AS $val) {
					if($val[thumb] != '' && is_file(PHPMPS_ROOT.$val[thumb])) {
						@unlink(PHPMPS_ROOT.$val[thumb]);
					}
				}
				$sql = "DELETE FROM {$table}com WHERE comid in ($id)";
				$res = $db->query($sql);
				
				//删除评论
				$sql = "DELETE FROM {$table}com_comment WHERE comid IN ($id)";
				$db->query($sql);

				//删除图片
				$sql = "SELECT path FROM {$table}com_image WHERE comid IN ($id)";
				$image = $db->getAll($sql);
				foreach((array)$image AS $val){
					if($val[path] != '' && is_file('../'.$val[path])){
						@unlink('../'.$val[path]);
					}
				}

				$sql = "DELETE FROM {$table}com_image WHERE comid IN ($id)";
				$db->query($sql);

				admin_log("删除记录 $id 成功");
				show('删除信息成功', $_SERVER['HTTP_REFERER']);
			break;

			case 'is_check':
				$sql = "UPDATE {$table}com SET is_check=1 WHERE comid IN ($id)";
				$re = $db->query($sql);
				admin_log("审核记录 $id 成功");
				show('审核成功', $_SERVER['HTTP_REFERER']);
			break;
		}
	break;
}
?>