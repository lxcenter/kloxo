<?php 

class rubyrails extends lxdb {
static $__desc = array("", "",  "rails_app");
static $__desc_nname = array("n", "",  "name");
static $__desc_appname = array("n", "",  "applicationname", "a=show");
static $__desc_rubyfcgiprocess_num  	 = array("hq", "",  "rubyfcgi:number_of_ruby_process");
static $__desc_accessible_directly = array("f", "",  "accessible_directly");
static $__desc_directory = array("n", "",  "path");
static $__desc_url = array("n", "",  "url");
static $__rewrite_nname_const =    Array("appname", "parent_clname");



static function createListNlist($parent, $view)
{
	$nlist['appname'] = '4%';
	$nlist['directory'] = '100%';
	$nlist['url'] = '100%';

	return $nlist;
}

function createExtraVariables()
{
	$this->__var_username = $this->getParentO()->username;
	$this->customer_name = $this->getParentO()->customer_name;
}

function updateform($subaction, $param)
{
	$parent = $this->getParentO();
	$vlist['appname'] = array('M', null);
	self::checkAlreadySet($this, $parent, $vlist);
	$vlist['rubyfcgiprocess_num'] = null;
	return $vlist;
}

function createShowUpdateform()
{
	$uflist['update'] = null;
	return $uflist;
}

static function add($parent, $class, $param)
{
	$parent->setUpdateSubaction('railsconf');
	return $param;
}

function update($subaction, $param)
{
	$this->getParentO()->setUpdateSubaction('railsconf');
	return $param;
}


static function checkAlreadySet($self, $parent, &$vlist)
{
	if ($self && $self->isOn('accessible_directly')) {
		$vlist['accessible_directly'] = null;
		return;
	}
	$list = $parent->getList('rubyrails');
	$already_set = false;
	foreach((array) $list as $l) {
		if ($l->isOn('accessible_directly')) {
			$already_set = true;
			break;
		}
	}
	if (!$already_set) {
		$vlist['accessible_directly'] = null;
	} else {
		$vlist['accessible_directly'] = array('M', 'another_application_exists');
	}
}
static function addform($parent, $class, $typetd = null)
{
	$vlist['appname'] = null;

	self::checkAlreadySet(null, $parent, $vlist);

	$vlist['rubyfcgiprocess_num'] = null;

	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}


function display($var)
{
	$this->customer_name = $this->getParentO()->customer_name;
	if ($var === 'directory') {
		return "/home/{$this->customer_name}/ror/{$this->getParentname()}/$this->appname";
	}
	if ($var === 'url') {
		if ($this->isOn('accessible_directly')) {
			return "http://{$this->getParentname()}/";
		} else {
			return "http://{$this->getParentname()}/$this->appname/";
		}
	}
	return parent::display($var);
}

}


