<?php 


class monitorserver extends Lxdb {

static $__desc = array("S", "",  "Server");
static $__desc_nname =  array("n", "",  "server_name");
static $__desc_servername =  array("n", "",  "server_address", "a=show");
static $__desc_description =  array("n", "",  "description", "a=show");
static $__desc_status =  array("e", "",  "s:monitor_status");
static $__desc_status_v_on =  array("n", "",  "monitored");
static $__desc_status_v_off =  array("n", "",  "not_monitored");
static $__desc_interval =  array("n", "",  "server_name");
static $__desc_portdescription =  array("", "",  "Port Status");
static $__rewrite_nname_const = array("servername", "parent_clname");

static $__desc_monitorport_num =   array("q","",  "number_of_monitoed_ports");
static $__acdesc_update_information =  array("","",  "information"); 
static $__desc_monitorport_l = array("dq", "", "");

static $__acdesc_list = array("", "", "port_monitor");

static $__desc_emailalert_l = array("", "", "");



function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'a=addform&dta[var]=atype&dta[val]=standard&c=monitorport';
	$alist['property'][] = 'a=addform&dta[var]=atype&dta[val]=general&c=monitorport';
	$alist['property'][] = 'a=updateform&sa=information';
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	$list = $parent->getList('monitorserver');

	if ($parent->get__table() !== 'vps' || !$list) {
		$alist[] = "a=addform&c=$class";
	}
	return $alist;
}
function createShowAlist(&$alist, $subaction = null)
{

	//$alist['__title_main'] = 'Main';
	//$this->getToggleUrl($alist);

	//$alist['property'][] = 'a=list&c=emailalert';
	return $alist;
}

static function defaultSort() { return 'description' ; }

function updateform($subaction, $param)
{
	switch($subaction) {
		case "information":
			{
				$vlist['description'] = null;
				return $vlist;
			}
	}
}

function createShowUpdateform()
{
	return null;
	$uflist[] = 'a=addform&dta[var]=atype&dta[val]=standard&c=monitorport';
	$uflist[] = 'a=addform&dta[var]=atype&dta[val]=general&c=monitorport';
	return $uflist;
}


function getId() { return strtilfirst($this->nname, "___"); }

static function createListNlist($parent, $view)
{
	$nlist['status'] = '5%';
	$nlist['servername'] = '10%';
	$nlist['description'] = '10%';
	$nlist['portdescription'] = '100%';
	return $nlist;
}

function display($var)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($var === 'portdescription') {
		$dir = get_image_path() . "/button";
		$text  = null;
		$list = $this->getList("monitorport");
		foreach($list as $p) {
			$ig = "_lximg:$dir/{$p->portstatus}.gif:10:10:";
			$text .= "{$p->portname}: $ig &nbsp; &nbsp; &nbsp;  ";
		}
		return $text;
	}
	return $this->$var;
}

function postAdd()
{
	$this->priv = new priv(null, null, $this->nname);
	$this->priv->monitorport_num = 'Unlimited';
}

static function addform($parent, $class, $typetd = null)
{
	if ($parent->get__table() === 'vps') {
		$vv = $parent->getNotExistingList($vlist, 'servername', 'monitorserver', 'vmipaddress_a');
		if ($vv) {
			$vlist['description'] = null;
		}
	} else {
		$vlist['servername'] = null;
		$vlist['description'] = null;
	}

	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

function isSync() { return false ;}

function createShowClist($subaction)
{
	$uflist['monitorport'] = null;

	return $uflist;
}

}
