<?php 

class domaindefault extends lxdb {

static $__desc_nname	 = array("n", "",  "domain_default");
static $__desc = array("n", "",  "domain_defaults");
static $__desc_remove_processed_stats = array("f", "",  "remove_processed_logs");
static $__acdesc_update_update = array("n", "",  "domain_defaults");

function updateform($subaction, $param)
{
	$vlist['remove_processed_stats'] = null;
	return $vlist;
}

}
