<?php 

class serverftp extends lxdb {


static $__desc = array("", "",  "FTP_config");
static $__acdesc_show = array("", "",  "FTP_config");

static $__desc_enable_anon_ftp = array("f", "", "enable_anonymous_ftp");
static $__desc_highport = array("", "", "high_port_for_passive_ftp");
static $__desc_lowport = array("", "", "low_port_for_passive_ftp");
static $__desc_maxclient = array("", "", "maximum_number_of_clients");
static $__acdesc_update = array("f", "", "update");


function createExtraVariables() 
{ 
	$this->setDefault();
}

function createShowUpdateform()
{
	$uflist['update'] = null;
	return $uflist;
}
function updateform($subaction, $param)
{

	$this->setDefault();
	$vlist['enable_anon_ftp'] = null;
	$vlist['maxclient'] = null;
	$vlist['lowport'] = null;
	$vlist['highport'] = null;
	return $vlist;
}


function setDefault()
{
	$this->setDefaultValue('lowport', "30000");
	$this->setDefaultValue('highport', "50000");
	$this->setDefaultValue('maxclient', "5000");
	$this->setDefaultValue('enable_anon_ftp', "on");
}

}
