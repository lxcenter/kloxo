<?php 


class monitorport extends Lxdb {

static $__desc = array("S", "",  "Port");
static $__desc_nname =  array("n", "",  "port");
static $__desc_portnumber =  array("n", "",  "port", "a=show");
static $__desc_portname =  array("n", "",  "Port Description", "a=show");
static $__desc_errorstring =  array("", "",  "Last Error");
static $__desc_portstatus =  array("e", "",  "Port Status");
static $__desc_portstatus_v_on =  array("", "",  "Port Up");
static $__desc_portstatus_v_off =  array("", "",  "Port Down");
static $__desc_updatetime  =  array("", "",  "Update_time");
static $__desc_changetime  =  array("", "",  "Port Status Changed At");
static $__desc_type =  array("n", "",  "port");
static $__desc_atype =     array("e", "",  "t:client_type");
	static $__desc_atype_v_standard =    array("", "",  "standard_port");
	static $__desc_atype_v_general =    array("", "",  "custom_port");

static $__rewrite_nname_const = array("portnumber", "parent_clname");

static $__desc_porthistory_l = array('d', '', '', '');
static $__desc_portstatus_l = array('d', '', '', '');


static function addform($parent, $class, $typetd = null)
{

	$standard_port = array("HTTP:80", "FTP:21", "SSH:22", "SMTP:25", "DNS:53", "POP3:110", "IMAP:143", "Mysql:3306");

	$list = $parent->getList('monitorport');
	foreach($list as $p) {
		$key = array_search("$p->portname:$p->portnumber", $standard_port);
		if ($key !== false) {
			unset($standard_port[$key]);
		}
	}

	if ($typetd['val'] === 'standard') {
		$vlist['portnumber'] = array('s', $standard_port);
	} else {
		$vlist['portnumber'] = null;
		$vlist['portname'] = null;
	}
	$clp = $parent->getClientParentO();
	if (!is_unlimited($clp->priv->monitorport_num) && $clp->used->monitorport_num >= $clp->priv->monitorport_num) {
		throw new lxException("mon_port_exceeded", "nname");
	}
		
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;

}

static function createListNlist($parent, $view)
{
	//$nlist['nname'] = '40%';
	$nlist['portstatus'] = '10%';
	$nlist['portname'] = '100%';
	$nlist['portnumber'] = '40%';
	$nlist['errorstring'] = '40%';
	$nlist['changetime'] = '10%';
	return $nlist;
}

function isSync() { return false ;}

function getId() { return strtilfirst($this->nname, "___"); }

static function createListAlist($parent, $class)
{
	$alist[] = 'a=show';
	//$alist[] = 'a=list&c=monitorport';
	$alist[] = 'a=addform&dta[var]=atype&dta[val]=standard&c=monitorport';
	$alist[] = 'a=addform&dta[var]=atype&dta[val]=general&c=monitorport';
	$alist[] = 'a=updateform&sa=information';
	//$alist[] = 'a=list&c=emailalert';
	return $alist;
}



function createShowClist($subaction)
{
	$clist['porthistory'] = null;
	//$clist['portstatus'] = null;
	return $clist;
}

function display($var)
{
	if ($var === 'updatetime') {
		return @ date('Y-M-d:::H:i', $this->updatetime);
	}

	if ($var === 'changetime') {
		return @ date('Y-M-d::::H:i', $this->changetime);
	}

	if ($var === 'errorstring') {
		if ($this->isOn('portstatus')) {
			return '-';
		}
		return $this->errorstring;
	}

	return $this->$var;
}

function postAdd()
{
	$this->checkAndUpdatePort();
}

function checkAndUpdatePort()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$num = $this->portnumber;
	$sname = $this->getParentO()->servername;

	$res = @ fsockopen($sname, $num, $erno, $erstr, 10);
	if (!$res) {
		$this->portstatus = 'off';
		$this->errornumber = $erno;
		$this->errorstring = $erstr;
	} else {
		$this->portstatus = 'on';
	}

	$this->portnname = $this->nname;

	$this->updatetime = time();
	$this->changetime = time();
	$this->setUpdateSubaction();

}

function checkPort()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$num = $this->portnumber;
	$sname = $this->getParentO()->servername;

	dprint("Checking Port $sname: $num\n");

	print_time("checkprot");
	$sip = gethostbyname($sname);

	if (validate_ipaddress($sip)) {
		$res =  fsockopen($sip, $num, $erno, $erstr, 10);
	} else {
		$res = null;
		$erno = 1;
		$erstr = "Dns failed";
	}

	print_time("checkprot", "Fsockopen");
	$name = $sgbl->thisserver . "___" . $this->nname;
	$obj = new PortStatus(null, "localhost", $name);
	$obj->initThisDef();

	if (!$res) {
		$obj->portstatus = 'off';
		$obj->errornumber = $erno;
		$obj->errorstring = $erstr;
	} else {
		fclose($res);
		$obj->portstatus = 'on';
	}

	$obj->portnname = $this->nname;

	$obj->servername = $sgbl->thisserver;
	$obj->updatetime = time();
	$obj->setUpdateSubaction();
	return $obj;
}



static function add($parent, $class, $param)
{
	$rparent = $parent->getParentO();

	if ($rparent->get__table() !== 'vps' && $rparent->priv->monitorport_num <= $rparent->used->monitorport_num) {
		throw new lxException("quota_exceeded_for_port", "portnumber");
	}

	if ($param['atype'] === 'standard') {
		$v = explode(':', $param['portnumber']);
		$param['portnumber'] = trim($v[1]);
		$param['portname'] = trim($v[0]);
	} else {
		$param['portnumber'] = trim($param['portnumber']);
		if (!is_numeric($param['portnumber'])) {
			throw new lxException("port_is_a_number", "portnumber");
		}
	}


	return $param;
}


}
