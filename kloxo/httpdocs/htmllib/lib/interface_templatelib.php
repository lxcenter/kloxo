<?php 

class interface_template extends lxdb {

static $__desc = array("", "",  "interface_template");
static $__desc_nname = array("n", "",  "interface_template_name", "a=show");
static $__acdesc_show_client = array("", "",  "client_interface");
static $__acdesc_show_domain = array("", "",  "domain_interface");
static $__acdesc_show_vps = array("", "",  "vps_interface");



function createShowPropertyList(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['property'][] = "a=show";
	$alist['property'][] = "a=show&sa=client";
	if ($sgbl->isKloxo()) {
		$alist['property'][] = "a=show&sa=domain";
	} else {
		$alist['property'][] = "a=show&sa=vps";
	}
}

function updateUpdate($param)
{
	foreach($param as $k => $v) {
		$param[$k] = self::fixListVariable($v);
	}
	dprintr($param);
	return $param;
}

function updateform($subaction, $param)
{
	$vlist['domain_show_list'] = null;
	$vlist['client_show_list'] = null;
	$vlist['vps_show_list'] = null;
	return $vlist;
}

function showRawPrint($subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$subaction) {
		return;
	}
	$class = $subaction;
	$var = "{$subaction}_show_list";
	$alist = exec_class_method($subaction, "get_full_alist");
	foreach ($alist as $k => $a) {
		if ($ghtml->is_special_url($a)) {
			$alist[$k] = $a->purl;
		}
	}

	$dst = null;
	foreach ( (array)$this->$var as $k => $v) {
		if (!csa($v, "__title")) {
			$dst[] = base64_decode($v);
		} else {
			$dst[] = $v;
		}
	}

	$ghtml->print_fancy_select($class, $alist, $dst);
}

static function initThisObjectRule($parent, $class, $name = null) 
{ 
	if ($parent->getSpecialObject('sp_specialplay')->interface_template) {
		return $parent->getSpecialObject('sp_specialplay')->interface_template;
	}
	return 'default';
}

static function initThisListRule($parent, $class)
{
	return "__v_table";
}


}
