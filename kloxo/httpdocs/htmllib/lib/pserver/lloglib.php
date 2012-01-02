<?php 

class Llog extends Lxclass {

static $__desc = array("", "",  "log_manager");
// Data
static $__desc_nname = array("", "",  "server_name", "a=show");
static $__acdesc_show = array("", "",  "log_manager", "a=show");


static $__desc_ffile_l = array('v', '', '', '');


function get() {}
function write() {}

static function initThisObjectRule($parent, $class, $name = null)
{
	return $parent->getClName();
}

function getId()
{
	return $this->getSpecialname();
}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'a=show&l[class]=ffile&l[nname]=/';
}

function createShowAlist(&$alist, $subaction = null)
{
	return $alist;
}

/** 
* @return void 
* @param 
* @param 
* @desc  Special getfromlist for ffile. The concept is that the the whole directory tree is available virtually under an ffile object, thus enabling us to get any object at any level. This is different from other objects where there is only one level of children.
*/ 
 
function getFfileFromVirtualList($name)
{
	$name = coreFfile::getRealpath($name);
	$name = '/' . $name;
	$ffile= new Ffile($this->__masterserver, $this->__readserver, "__path_log", $name, $this->getParentO()->username);
	$ffile->__parent_o = $this;
	$ffile->get();
	$ffile->readonly = 'on';
	return $ffile;
}


function createShowSclist()
{

	$sclist['ffile'] = array('kloxo/maillog' => 'Maillog', "kloxo/smtp.log" => "SMTP.log", 'httpd/access_log' => 'Http Log', 'mysqld.log' => 'Mysql Log');
	return $sclist;
}



}
