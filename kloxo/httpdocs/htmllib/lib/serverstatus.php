<?php 

class serverstatus extends lxclass {




static function createListNlist($parent, $view)
{
	$nlist['nname'] = '100%';
	$nlist['data'] = '10%';
	return $nlist;
}

static function initThisListRule($parent, $class) { return null; }
static function initThisList($parent, $class)
{

}

}
