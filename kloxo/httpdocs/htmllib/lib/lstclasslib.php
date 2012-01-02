<?php 

class lstclass {

static $__desc = array("", "",  "class_list");
static $__desc_lstclass_list	 = array("U", "",  "client_name");



static function getNname($parent, $class)
{
	return "$class-{$parent->getClName()}";
}

static function getParams()
{
	$name_list["status"] = "3%";
	$name_list["dtype"] = "3%";
	$name_list["nname"] = "100%";
	$name_list["parent_name_f"] = "5%";
	$name_list["ddate"] = "20%";
	$name_list["traffic_usage"] = "5%";
	//$name_list["totaldisk_usage"] = "5%";
	$name_list["pvview_f"] = "3%";
	$name_list["dnvview_f"] = "3%";
	$name_list["webmail_f"] = "3%";
	$name_list["stats_f"] = "3%";
	$name_list["awstats_f"] = "3%";
}

function updateform($subaction, $param)
{
	
	$vlist['lstclass_list'] = array('U', $list);
	return $vlist;
}

}
