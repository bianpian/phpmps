<?php
define('IN_PHPMPS', true);

$nav = get_nav(); //导航
$cats_list  = get_cat_list();
$areas_list = get_area_list();

$area_option = area_options(); //地区下拉菜单
$cat_option  = cat_options(); //分类下拉菜单
$about = get_about();
?>