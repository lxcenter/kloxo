<?php 


class HelpDesk  extends Lxclass {

static $__desc = array("", "",  "help_desk");


function write() { }
function get() { }

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$class = "ticket";

	$alist['__title_main'] = $login->getKeywordUc('resource');

	$alist[] = "a=list&c=$class&frm_filter[show]=nonclosed";
	$alist[] = "a=list&c=$class&frm_filter[show]=open";
	$alist[] = "a=list&c=$class&frm_filter[show]=all";
}


static function initThisObjectRule($parent, $class, $name = null) { return null; }

static function initThisObject($parent, $class, $name = null)
{
	$o = new HelpDesk($parent->__masterserver, $parent->__readserver, $parent->nname);

	return $o;
}
	


}
