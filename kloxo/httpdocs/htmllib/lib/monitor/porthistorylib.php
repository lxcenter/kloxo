<?php 

class porthistory extends lxdb {


static $__desc = array("S", "",  "Port Status History");
static $__desc_nname =  array("n", "",  "server_name");
static $__desc_portnumber =  array("n", "",  "port");
static $__desc_ddate =  array("n", "",  "date");
static $__desc_portname =  array("n", "",  "Port Description");
static $__desc_errorstring =  array("", "",  "Last Error");
static $__desc_laststatustime =  array("", "",  "Last Status Period");
static $__desc_portstatus =  array("e", "",  "Port Status");
static $__desc_portstatus_v_on =  array("e", "",  "On");
static $__desc_portstatus_v_off =  array("e", "",  "Off");

static $__rewrite_nname_const = array("ddate", "parent_clname");



static function createListNlist($parent, $view)
{
	$nlist['portstatus'] = "10%";
	$nlist['ddate'] = '10%';
	$nlist['laststatustime'] = '30%';
	$nlist['errorstring'] = '100%';
	return $nlist;

}

static function defaultSort() { return 'ddate' ; }
static function defaultSortDir() { return 'desc' ; }

function display($var)
{
	if ($var === 'ddate') {
		return @ date('Y-M-d:H:i:s', $this->ddate);
	}
	if ($var === 'laststatustime') {
		if ($this->isOn('portstatus')) {
			$statevar = "Downtime";
		} else {
			$statevar = "Uptime";
		}
		return round(($this->$var)/60, 1) . " Minutes $statevar";
	}

	return $this->$var;
}

static function initThisListRule($parent, $class)
{
	return array('portnname', '=', "'{$parent->nname}'");
}



}
