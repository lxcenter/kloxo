<?php 


class ftpclient extends lxclass {


function __construct($masterserver, $readserver, $rmuser, $rmpass, $rmdir, $name) 
{
	$this->rmuser = $rmuser;
	$this->rmpass = $rmpass;
	$this->rmdir = $rmdir;
	parent::__construct(null, null, $name);
}




function get()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	static $st;


	if (isset($this->download_f) && $this->download_f) {
		$numlines = 'download';
	} else {
		if ($this->getParentO()->is__table('llog')) {
			$numlines = 20;
		} else {
			$numlines = null;
		}
	}
	if ($st > 0) {
		print("Called more than once\n");
	}
	$st++;

	$this->duflag = $gbl->getSessionV('ffile_duflag');
	$gbl->setSessionV('ffile_duflag', false);
	$this->numlines = $numlines;
	$stat = rl_exec_get(null, $this->__readserver,  array("coreFfile", "getLxStat"), array($this->__username_o, $this->getFullPath(), $numlines, $this->duflag));

	//dprintr($stat);

	if (!isset($this->readonly)) { $this->readonly = 'off'; }


	$this->setFromArray($stat);
	if (!$this->isOn('readonly')) {
		$this->__flag_showheader = true;
	}
	$this->setFileType();
}


static function initThisList($parent, $class)
{

	$fpathp = $parent->fullpath;


	if (!$parent->is_dir()) {
		return null;
	}

	$duflag = $parent->duflag;

	$list = rl_exec_get($parent->__masterserver, $parent->__readserver,  array("coreFfile", "get_full_stat"), array($parent->__username_o, $fpathp, $duflag));


	foreach((array) $list as $stat) {
		$file = basename($stat['name']);
		if ($file === "") {
			continue;
		}
		if ($file === ".")
			continue;

		$fpath = $fpathp . "/" . $file;

		$file = $parent->nname . "/" . $file;
		if (!isset($parent->ffile_l)) {
			$parent->ffile_l = null;
		}
		$parent->ffile_l[$file] = new Ffile($parent->__masterserver, $parent->__readserver,  $parent->root, $file, $parent->__username_o);
		$parent->ffile_l[$file]->setFromArray($stat);
		$parent->ffile_l[$file]->__parent_o = $parent->getParentO();
		$parent->ffile_l[$file]->setFileType();

	}
	$__tv = null;
	return $__tv;
}


}
