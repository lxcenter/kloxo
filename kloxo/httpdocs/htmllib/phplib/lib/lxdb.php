<?php


abstract class Lxdb extends Lxclass {

static $__table;

static $__desc_olddeleteflag = array('', '', "last_switch_status", "");
static $__desc_restore_file_f = array("F", "",  "Backup_File");

public function __construct($masterserver, $readserver, $nname)
{
	//if (!$this->__table) {
		//$this->__table = lget_class($this);
	//}

	parent::__construct($masterserver, $readserver, $nname);
}

// This should ideally be final, but I need to override this in superadmin, which inherits from lxclient. Ok later made final again.... Redesigned supernode stuff.
final public function write()
{
	return $this->writeToDb();
}

public function get()
{
	return $this->getThisFromDb();
}

static function isDatabase() { return true; }

static function initThisObjectRule($parent, $class, $name = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$objectname = $class . "_o";
	$desc = get_classvar_description(get_class($parent),  $objectname);
	if (!$desc) {
		if ($login->isAdmin()) {
			print("<h1> Trying to init and non described  object $objectname in " . get_class($parent) . "...</h1>  <br> \n");
			//exit(0);
		} else {
			dprint("<h1> Trying to init and non described  object $objectame in " . get_class($parent) . "... </h1> <br> \n");
			print("Attempt to Hack.... <br> ");
			//exit(0);
		}
	}

	if (!$name) {
		$name = $parent->nname;
	}
	return $name;
}


function checkChildExists($class)
{
	$listvar = $class . "_l";
	global $gbl, $sgbl, $login, $ghtml; 
	$desc = get_classvar_description(get_class($this),  $listvar);



	if (!$desc) {
		if ($login->isAdmin()) {
			print("<h1> Trying to init and non described list $class in " . get_class($this) . "... </h1> <br> \n");
			//$trace = debug_backtrace();
			//dprint(DBG_GetBacktrace($trace));
			//exit;
		} else {
			dprint("<h1> Trying to init and non described list $class in " . get_class($this) . "... </h1> <br> \n");
			print("Attempt to Hack.... <br> ");
			//exit;
		}
	}
}

static function canGetSingle()
{
	return true;
}

static function initThisListRule($parent, $class)
{

	$listvar = "{$class}_l";

	$parent->checkChildExists($class);


	$ret[] = array('parent_clname', '=', "'{$parent->getClName()}'");
	return $ret;


}

static function initThisOutOfBand($parent, $iclass, $mclass, $rclass)
{
	$sq = new Sqlite(null, $iclass);
	$res = $sq->getRowsWhere("parent_clname = '{$parent->getClName()}'", array("nname"));
	$res = get_namelist_from_arraylist($res);
	$ret = null;
	foreach($res as $r) {
		$ret[] = "or";
		$ret[] = array('parent_clname', '=', "'$mclass-$r'");
	}
	unset($ret[0]);
	return $ret;
}


function updateform($subaction, $param)
{

	global $gbl, $sgbl, $login, $ghtml; 
	switch($subaction) {
		case "switchserver":
			$serverlist = $login->getServerList($this->get__table());
			if (!$this->checkIfLockedForAction('switchserver')) {
				if ($this->olddeleteflag === 'doing') {
					$this->olddeleteflag = 'program_interrupted';
				}
			}
			$vlist['olddeleteflag'] = array('M', null);
			$psi = pserver::createServerInfo($serverlist, $this->get__table());
			$psi = get_warning_for_server_info($login, $psi);
			$vlist['server_detail_f'] = array('M', $psi);
			$vlist['syncserver'] = array('s', $serverlist);
			return $vlist;

		case "restore":
			$vlist['restore_file_f'] = null;
			$sgbl->method = 'post';
			return $vlist;

		case "restore_from_http":
			$vlist['restore_url_f'] = null;
			return $vlist;
	}

	return parent::updateform($subaction, $param);
}

function checkIfLockedForAction($action)
{
	return lx_core_lock_check_only("$action.php", "{$this->get__table()}-$this->nname.$action");
}

function makeDnsChanges($newserver) { }

function updateSwitchServer($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->checkIfLockedForAction('switchserver')) {
		throw new lxException("switch_is_already_happening", "syncserver", $param['syncserver']);
	}

	$this->checkNotSame($param, array("syncserver"));
	$this->olddeleteflag = 'doing';

	$this->setUpdateSubaction();
	$this->write();
	rl_exec_get($this->__masterserver, 'localhost', array($this->get__table(), "exec_switchserver"), array($this->get__table(), $this->nname, $param));

	// Needs this because the nname can change when the syncserver is changed. Nname never changes when you switch server. Need to make sure of that.
	/*
	if (isset($this->__real_nname)) {
		$url = $ghtml->getFullUrl("goback=1&a=show&l[class]={$this->get__table()}&l[nname]=$this->nname");
		dprint($this->nname);
		$ghtml->print_redirect("$url&frm_smessage=switch_done");
	} else {
		*/
	$ghtml->print_redirect_back_success("switch_done", null);
	exit;

}

function updateLiveMigrate($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->checkIfLockedForAction('livemigrate')) {
		throw new lxException("livemigrate_is_already_happening", "syncserver", $param['syncserver']);
	}

	$this->checkNotSame($param, array("syncserver"));
	$this->olddeleteflag = 'doing';

	$this->setUpdateSubaction();
	$this->write();
	rl_exec_get($this->__masterserver, 'localhost', array($this->get__table(), "exec_livemigrate"), array($this->get__table(), $this->nname, $param));

	// Needs this because the nname can change when the syncserver is changed. Nname never changes when you switch server. Need to make sure of that.
	/*
	if (isset($this->__real_nname)) {
		$url = $ghtml->getFullUrl("goback=1&a=show&l[class]={$this->get__table()}&l[nname]=$this->nname");
		dprint($this->nname);
		$ghtml->print_redirect("$url&frm_smessage=switch_done");
	} else {
		*/
	$ghtml->print_redirect_back_success("switch_done", null);
	exit;

}

static function exec_switchserver($class, $name, $param)
{
	lxshell_background("__path_php_path", "../bin/common/switchserver.php", "--priority=low", "--class=$class", "--name=$name", "--v-syncserver={$param['syncserver']}");
}

static function exec_livemigrate($class, $name, $param)
{
	lxshell_background("__path_php_path", "../bin/common/livemigrate.php", "--priority=low", "--class=$class", "--name=$name", "--v-syncserver={$param['syncserver']}");

}


function doupdateSwitchserver($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$this->checkNotSame($param, array("syncserver"));

	if (!exists_in_db($this->__masterserver, 'pserver', $param['syncserver'])) {
		throw new lxException("does_not_exist", "syncserver", $param['syncserver']);
	}

	$this->__var_bc_backupextra_stopvpsflag = 'on';
	if ($this->extraBackup()) {
		$file = $this->backMeUpThere();
		$this->subaction = null;
	}

	$oldsyncserver = $this->syncserver;

	$this->olddeleteflag = 'done';

	try {
		$cloned  = clone($this);
		$this->AddToThere($param['syncserver']);
		$this->was();
		if ($this->extraBackup()) {
			dprint("Got the backed up file...\n");
			dprint("$oldsyncserver\n");
			dprintr($file);
			$this->restoreMeUpThere($oldsyncserver, $file);
		}

		//$this->makeDnsChanges($param['syncserver']);
		$this->UpdateHeirarchy();
		$this->was();
		// There is a problem here. Teh __list_list will get cleared with one was. Then it is the getlist called from inside the deletefromhere that should fill it up again.

		$cloned->DeleteFromHere($oldsyncserver);
		$cloned->was();

	} catch (exception $e) {
		throw $e;
	}


}

final protected function getThisFromDb()
{
	$dbacc = new Sqlite($this->__masterserver, $this->get__table());
	
	$result = $dbacc->getRows("nname", $this->nname);


	if (!$result) {
		$this->initThisDef();
		$this->dbaction = "add" ;
		return 0;
	}

	$this->setFromArray($result[0]);

	return 1;

}


final function writeToDb()
{ 


	$dbupdate =new Sqlite($this->__masterserver, $this->get__table());


	switch($this->dbaction) {

		case "delete":
			$dbupdate->delRow("nname", $this->nname);
			break;

		case "add" :
			$dbupdate->addRowObject($this);
			break;

		case "update":
			if (isset($this->__real_nname)) {
				$dbupdate->setRowObject("nname", $this->__real_nname, $this);
			} else {
				$dbupdate->setRowObject("nname", $this->nname, $this);
			}
			break;

	}


	return 1;
}
}


class LxspecialClass extends Lxdb {


function isSync()
{
	return false;
}

static function initThisObjectRule($parent, $class, $name = null)
{
	return $parent->getClName();
}

function getId()
{
	return $this->getSpecialname();
}

}


