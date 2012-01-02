<?php 

class MonitoringServerStatus extends Lxdb {


static $__desc = array("S", "",  "Kloxo License");
static $__desc_nname =  array("n", "",  "Kloxo license", "a=show");
static $__desc_updatetime  =  array("", "",  "Update_time");



static function initThisObjectRule($parent, $class, $name = null)
{
	$nname = $parent->servername;
	return $nname;
}

function isSync() { return false; }

}


