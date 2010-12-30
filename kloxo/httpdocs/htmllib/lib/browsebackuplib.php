<?php 

class browsebackup extends Lxclass {

static $__desc = array("", "",  "browse_backup");
static $__acdesc_show = array("", "",  "browse_backup");

static function initThisObjectRule($parent, $class, $name = null) { return $parent->nname ; }

function get() {}
function write() {}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'a=show&l[class]=ffile&l[nname]=/';
}

function getFfileFromVirtualList($name)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$parent = $this->getParentO();


	$bserver = $parent->getBackupServer();

	if (is_disabled_or_null($bserver)) {
		throw new lxException("backup_server_is_not_configured");
	}

	$bs = new CentralBackupServer(null, null, $bserver);
	$bs->get();

	if ($bs->dbaction === 'add') {
		throw new lxException("backup_server_is_not_there");
	}

	$server = $bs->slavename;
	$root = "$bs->snapshotdir/vps/$parent->ttype/$parent->nname/";

	$name = coreFfile::getRealpath($name);
	$name = "/$name";

	$ffile= new Ffile(null, $server, $root, $name, "root");
	$ffile->__parent_o = $this;
	$ffile->get();
	$ffile->browsebackup = 'on';
	return $ffile;
}



}
