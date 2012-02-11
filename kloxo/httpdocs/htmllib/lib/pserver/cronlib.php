<?php


class Cron extends Lxdb {
static $__table =  'cron';

//Core
static $__desc = array("", "",  "cron scheduled_task");

static $__desc_nname = array("", "",  "min");
static $__desc_minute = array("", "",  "minute");
static $__desc_hour   = array("", "",  "hour");
static $__desc_weekday	  = array("", "",  "day_of_week");
static $__desc_ddate  = array("", "",  "date");
static $__desc_month  = array("", "",  "month" );
static $__desc_command= array("n", "",  "command", URL_SHOW);
static $__desc_ttype_v_simple = array("", "",  "simple");
static $__desc_ttype_v_complex = array("", "",  "standard");
static $__desc_ttype = array("", "",  "type");
static $__desc_cron_day_hour = array("", "",  "if_every_day_the_hour");
static $__desc_simple_cron = array("", "",  "period");
static $__desc_argument = array("", "",  "argument");
static $__desc_username = array("", "",  "user_name");
static $__desc_syncserver = array("", "",  "syncserver");
static $__desc_mailto	 = array("", "",  "");
static $__acdesc_update_update = array("", "",  "cron scheduled_task");

static $minutelist = null;
static $hourlist = null;
static $ddatelist = null;
static $monthlist = null;
static $weekdaylist = null;

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	$alist[] = "a=addform&c=$class&dta[var]=ttype&dta[val]=simple";
	$alist[] = "a=addform&c=$class&dta[var]=ttype&dta[val]=complex";
	return $alist;
}

function __construct($masterserver, $readserver, $name)
{
	if (!self::$minutelist) {

		self::$minutelist[] = '--all--';

		foreach(range(0,59) as $i) {
			self::$minutelist[] = $i;
		}

		self::$hourlist[] = '--all--';

		foreach(range(0,23) as $i) {
			self::$hourlist[] = $i;
		}
		self::$ddatelist[] = '--all--';

		foreach(range(1,31) as $i) {
			self::$ddatelist[] = $i;
		}

		self::$weekdaylist[] = '--all--';
		self::$weekdaylist[] = 'sunday';
		self::$weekdaylist[] = 'monday';
		self::$weekdaylist[] = 'tuesday';
		self::$weekdaylist[] = 'wednesday';
		self::$weekdaylist[] = 'thursday';
		self::$weekdaylist[] = 'friday';
		self::$weekdaylist[] = 'saturday';

		self::$monthlist[] = '--all--';
		self::$monthlist[] = 'January';
		self::$monthlist[] = 'February';
		self::$monthlist[] = 'March';
		self::$monthlist[] = 'April';
		self::$monthlist[] = 'May';
		self::$monthlist[] = 'June';
		self::$monthlist[] = 'July';
		self::$monthlist[] = 'August';
		self::$monthlist[] = 'September';
		self::$monthlist[] = 'October';
		self::$monthlist[] = 'November';
		self::$monthlist[] = 'December';
	}

	parent::__construct($masterserver, $readserver, $name);

}
	
//Objects


function createExtraVariables()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$this->__var_mailto = $this->getParentO()->cron_mailto;
	$mydb = new Sqlite($this->__masterserver, "cron");
	$parent = $this->getParentO();
	$this->__var_cron_list = $mydb->getRowsWhere("username = '{$parent->username}'");

	$mydb = new Sqlite($this->__masterserver, "uuser");
	$userlist = $mydb->getRowsWhere("nname = '{$parent->username}'");
	$this->__var_user_list = $userlist[0];


}


static function  createListNlist($parent)
{
	//$nlist["nname"] = "5%";
	//$nlist["minute"] = "5%";
	//$nlist["hour"] = "5%";
	//$nlist["ddate"] = "5%";
	//$nlist["weekday"] = "5%";
	//$nlist["month"] = "5%";
	//$nlist["syncserver"] = "5%";
	$nlist["username"] = "10%";
	$nlist["command"] = "100%";

	return $nlist;
}

function postUpdate()
{
	if (!$this->isSimple()) {
		$this->convertAll();
		$this->checkIfNull();
	}
}

function isSimple()
{
	return ($this->ttype === 'simple');
}

function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$parent = $this->getParentO();

	// This is a hack to fix the cron migrated from web to client.
	if ($this->parent_clname !== $parent->getClName()) {
		$this->parent_clname = $parent->getClName();
		$this->setUpdateSubaction();
		$this->write();
	}

	if ($this->isSimple()) {
		$vlist['simple_cron'] = array('M', $this->simple_cron);
		if ($this->simple_cron === 'every-day') {
			$vlist['cron_day_hour'] = array('M', $this->cron_day_hour);
		}
		return $vlist;
	}

	$this->convertBack();
	$vlist["username"] = array('M', $this->username);

	if ($parent->isClass('pserver') || $parent->getClientParentO()->priv->isOn('cron_minute_flag')) {
		$vlist['minute'] = array('U', cron::$minutelist);
	} else {
		$vlist['minute'] = array('M', $this->minute[0]);
	}

	$vlist["hour"] = array('U', cron::$hourlist);
	$vlist["ddate"] = array('U', cron::$ddatelist);
	$vlist["weekday"] = array('U', cron::$weekdaylist);
	$vlist["month"] =  array('U', cron::$monthlist);
	$vlist["command"] = null;
	$driverapp = $gbl->getSyncClass($this->__masterserver, $this->__readserver, 'cron');
	if ($driverapp === 'windows') {
		$vlist["argument"] = array('M', null);
	}
	return $vlist;
}

static function addform($parent, $class, $typetd = null)
{

	global $gbl, $sgbl, $login, $ghtml; 
	// This is to make sure that the static variables 'monthlist, weekdaylist' etc, are initialized. There is no other way to do it.
	$tmp = new Cron($parent->__masterserver, $parent->__readserver, '__tmp__');

	if ($typetd['val'] === 'simple') {
		if ($parent->isClass('pserver') || $parent->priv->isOn('cron_minute_flag')) {
			$list['every-minute'] = 'Every Minute';
		}
		$list['every-hour'] = 'Every Hour';
		$list['every-day'] = 'Every Day';
		$vlist['simple_cron'] = array('A', $list);
		$v = self::$hourlist;
		unset($v[0]);
		$vlist['cron_day_hour'] = array('s', $v);
		$vlist['command'] = null;
		$ret['action'] = 'add';
		$ret['variable'] = $vlist;
		return $ret;
	}

	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'cron');

	if ($driverapp === 'windows') {
		$str = "s";
	} else {
		$str = "U";
	}


	$vlist["username"] = array('M', $parent->username);

	if ($parent->isClass('pserver') || $parent->priv->isOn('cron_minute_flag')) {
		$vlist['minute'] = array($str, cron::$minutelist);
	} else {
		$vlist['minute'] = array('m', 0);
	}

	$vlist["hour"] = array($str, cron::$hourlist);
	/*
	if ($driverapp === 'linux') {
	} else {
		$hourlist = cron::$hourlist;
		unset($hourlist[0]);
		$vlist["hour"] = array('s', $hourlist);
	}
*/

	$vlist["ddate"] = array($str, cron::$ddatelist);
	$vlist["weekday"] = array($str, cron::$weekdaylist);
	$vlist["month"] =  array($str, cron::$monthlist);
	/*
	if ($driverapp === 'linux') {
		$vlist["month"] =  array($str, cron::$monthlist);
	}
*/
	$vlist["command"] = null;
	if ($driverapp === 'windows') {
		$vlist["argument"] = null;
	}

	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;

}

function createShowUpdateform()
{
	$ulist['update'] = null;
	return $ulist;
}

function checkIfAll($v)
{
	return ($v === '--all--');
}

function convertAll()
{
	$this->month = cron::convertCronList($this->month, cron::$monthlist);
	$this->weekday = cron::convertCronList($this->weekday, cron::$weekdaylist);
	$this->ddate = cron::convertCronList($this->ddate, null);
	$this->hour = cron::convertCronList($this->hour, null);
	$this->minute = cron::convertCronList($this->minute, null);
}

function convertBack()
{
	$this->month = cron::convertBackCronList($this->month, cron::$monthlist);
	$this->weekday = cron::convertBackCronList($this->weekday, cron::$weekdaylist);
	$this->ddate = cron::convertBackCronList($this->ddate, null);
	$this->hour = cron::convertBackCronList($this->hour, null);
	$this->minute = cron::convertBackCronList($this->minute, null);
}

static function convertBackCronList($list, $staticlist)
{
	if ($list[0] === '--all--') {
		return $list;
	}
	
	foreach($list as $l) {
		if ($staticlist) {
			$outl[] = $staticlist[$l];
		} else {
			$outl[] = $l;
		}
	}
	return $outl;
}

static function convertCronList($string, $staticlist)
{
	if (is_array($string)) { return $string; }

	$string = trim($string);
	$string = trim($string, ",");
	$list = explode(",", $string);
	foreach($list as $l) {
		if ($l == '--all--') {
			$nel = null;
			$nel[] = '--all--';
			break;
		}
		if ($staticlist) {
			$nel[] = array_search($l, $staticlist);
		} else {
			$nel[] = $l;
		}
	}
	return $nel;
}



static function add($parent, $class, $param)
{
	if (!($parent->isClass('pserver') || $parent->priv->isOn('cron_minute_flag'))) {
		if (!is_numeric($param['minute'])) {
			$param['minute'] = 0;
		}
	}


	$param['username'] = $parent->username;
	/*
	if ($parent->is__table('pserver')) {
		$param['syncserver'] = $parent->nname;
	} else {
		$param['syncserver'] = $parent->syncserver;
	}
*/
	$parambase = implode("_", array($param['username'], $param['command']));
	$parambase = fix_nname_to_be_variable($parambase);
	$cronlist = $parent->getList('cron');
	$count = 0;


	while(isset($cronlist[$parambase . "_" . $count])) {
		$count++;
	}
	$param['nname'] = $parambase . "_" . $count;

	return $param;
}

function postAdd()
{
	if (!$this->isSimple()) {
		$this->checkIfNull();
		$this->convertAll();
	}
}

function checkIfNull()
{

	$this->checkIfNullVar('minute');
	$this->checkIfNullVar('hour');
	$this->checkIfNullVar('ddate');
	$this->checkIfNullVar('weekday');
	$this->checkIfNullVar('month');
}

function checkIfNullVar($var)
{
	if (is_array($this->$var)) {
		return;
	}
	if (trim($this->$var) === "") { 
		throw new lxexception("cannot_be_null", $var, "");
	}
}


static function createListUpdateForm($object, $class)
{
	$update[] = 'cron_mailto';
	return $update;
	
}

static function initThisListRule($parent, $class)
{
	if ($parent->is__table('pserver')) {
		$res[] = array('syncserver', '=', "'$parent->nname'");
		return $res;
		//$res[] = 'AND';
	} 

	$res[] = array("username", '=', "'$parent->username'");
	return $res;
}





}


class all_cron extends cron {

static $__desc = array("", "",  "all_scheduled_task");
static $__desc_parent_name_f =  array("n", "",  "owner");
static $__desc_parent_clname =  array("n", "",  "owner");

function isSelect() { return false ; }
static function createListAlist($parent, $class)
{
	return all_mailaccount::createListAlist($parent, $class);
}

static function initThisListRule($parent, $class)
{
	if (!$parent->isAdmin()) {
		throw new lxexception("only_admin_can_access", '', "");
	}

	return "__v_table";
}

static function createListUpdateForm($object, $class)
{
	return null;
}

static function createListSlist($parent)
{
	$nlist['nname'] = null;
	$nlist['parent_clname'] = null;
	return $nlist;
}

}

