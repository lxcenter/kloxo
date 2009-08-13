<?php


class information extends LxaClass {

static $__desc = array("", "",  "Information");
static $__desc_nname = array("", "",  "name");
static $__desc_variable = array("S", "",  "information");
static $__desc_value = array("S", "",  ".");

function isSelect() { return false; }
static function perPage() { return 5000; }


static function createListNlist($parent, $view)
{

	$nlist['variable'] = '50%';
	$nlist['value'] = '100%';
	return $nlist;
}

static function initThisList($parent, $class)
{
	$list = $parent->createShowInfoList("");

	$i = 0;
	foreach($list as $k => $l) {
		$r['nname'] = $i;
		$r['variable'] = $k;
		$r['value'] = $l;
		$res[] = $r;
		$i++;
	}

	$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'information', $res, true);
}


}

class PermissionOrResource extends LxaClass {




function isSelect() { return false; }
static function perPage() { return 5000; }

static function createListNlist($parent, $view)
{

	$nlist['state'] = '4%';
	$nlist['descr'] = '100%';
	$nlist['resourceused'] = '10%';
	if ($parent->isLxclient()) {
		$nlist['resourcepriv'] = '10%';
		$nlist['resourceusedper'] = '40%';
	}
	return $nlist;

}

function getMultiUpload($var) { return $var; }
function display($var)
{
	if ($var === 'state') {
		if ($this->resourcepriv === '-') {
			return 'ok';
		}
		if ($this->resourceused === 'NA') {
			return 'ok';
		}
		if (isQuotaGreaterThanOrEq($this->resourceused, $this->resourcepriv)) {
			return 'exceed';
		}
		return 'ok';
	}

	if (($this->vv === 'disk' || $this->vv === 'lvm' || cse($this->vv, "usage")) && ($var === 'resourceused' || $var === 'resourcepriv')) {
		return $this->privdisplay($this->vv, $var, $this->$var);
	}

	return $this->$var;
}

static function privdisplay($name, $var, $value)
{

	// We have to divide by 1024 * 1024 because prividsplay deals in MBs while we have got bytes.
	if ($name === 'sizeper') {
		$value = round($value/ (1024 * 1024), 2);
	}

	if (array_search_bool($name, array('sizeper', 'disk_usage', "maildisk_usage", "memory_usage", "backup_num", "traffic_usage", "traffic_last_usage", "swap_usage", "guarmem_usage", "realmem_usage", "disk", "lvm", "mysqldb_usage", "clientdisk_usage", "totaldisk_usage"))) {

		if ($value === '-' || is_unlimited($value)) {
			return $value; 
		}

		return getGBOrMB($value);
	}

	return $value;
}

static function searchVar() { return 'descr'; }

function getId() { return $this->descr; }


static function baseinitThisList($parent, $class, $type)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$rlist = $parent->createShowRlist("");
	$class = $parent->get__table();


	$resourceout = null;
	if ($rlist)  {
		$j = 0;
		foreach($rlist as $k => $v) {
			if ($k === 'priv') {
				$vlist = $parent->getQuotaVariableList();
				$vlist = lx_array_merge(array($vlist, $parent->getDeadQuotaVariableList()));
				foreach($vlist as $nk => $nv) {

					// Why am I skipping hardquota?. OK it screws up vps graph
					if ($parent->isHardQuota($nk)) {
						continue;
					}
					if ($type === 'permission') {
						if (!cse($nk, "_flag")) {
							continue;
						}
						if ($login->getSpecialObject('sp_specialplay')->isOn('dont_show_disabled_permission')) {
							if (!$parent->priv->isOn($nk)) {
								continue;
							}
						}


					} else {
						if (cse($nk, "_flag")) {
							continue;
						}
					}

					$desc = get_classvar_description($class, $nk);


					if (is_array($parent->priv->$nk)) {
						foreach($parent->priv->$nk as $nnk => $nnv) {
							$resourceout[$j]['vv'] = $nk;
							$resourceout[$j]['nname'] = $j;
							$resourceout[$j]['shortdescr'] = getNthToken($desc[2], 0);
							$sh = getNthToken($desc[2], 0);
							$ln = getNthToken($desc[2], 1);
							$resourceout[$j]['descr'] = "_lxspan:$sh:$ln:";
							$resourceout[$j]['resourceused'] = $parent->used->{$nk}[$nnk];
							$resourceout[$j]['resourcepriv'] = $parent->priv->{$nk}[$nnk];
							$j++;
						}
						continue;
					}


					if (isset($parent->used)) {
						$vresourceused = $parent->used->$nk;
					} else {
						$vresourceused = '-';
					}

					if (cse($nk, "_flag")) {
						$vresourcepriv = $parent->priv->$nk;
					} else {

						if ($parent->priv->$nk === "0") {
							continue;
						}
						if ($parent->showPrivInResource()) {
							$vresourcepriv = $parent->priv->$nk;
						} else {
							$vresourcepriv = '-';
						}
					}

					if (cse($nk, 'last_usage')) {
						$vresourcepriv = '-';
					}

					if ($parent->isDeadQuotaVariable($nk)) {
						$vresourcepriv = "-";
					}


					$resourceout[$j]['vv'] = $nk;
					$resourceout[$j]['nname'] = $j;
					$resourceout[$j]['shortdescr'] = getNthToken($desc[2], 0);
					$sh = getNthToken($desc[2], 0);
					$ln = getNthToken($desc[2], 1);
					$resourceout[$j]['descr'] = "_lxspan:$sh:$ln:";
					$resourceout[$j]['resourceused'] = $vresourceused;
					$resourceout[$j]['resourcepriv'] = $vresourcepriv;
					$j++;
				}
			} else {
				$resourceout[$j]['vv'] = $v[0];
				$resourceout[$j]['nname'] = $j;
				$resourceout[$j]['shortdescr'] = getNthToken($v[1], 0);
				$sh = getNthToken($v[1], 0);
				$ln = getNthToken($v[1], 1);
				$resourceout[$j]['descr'] = "_lxspan:$sh:$ln:";
				$resourceout[$j]['resourceused'] = $v[2];
				$resourceout[$j]['resourcepriv'] = $v[3];
				$j++;
			}

		}
	}


	return $resourceout;
}



}


class Permission extends PermissionOrResource {

static $__desc = array("", "",  "permission");
static $__desc_state = array("eS", "",  "s");
static $__desc_state_v_ok = array("", "",  "under_limit");
static $__desc_state_v_exceed = array("", "",  "exceeded" );
static $__desc_nname = array("", "",  "name");
static $__desc_descr = array("S", "",  "permission");
static $__desc_resourceused = array("", "",  "resourceused");
static $__desc_resourcepriv = array("eS", "",  "S");
static $__desc_resourcepriv_v_on = array("", "",  "status_is_on");
static $__desc_resourcepriv_v_off = array("", "",  "status_is_off");

static function createListNlist($parent, $view)
{

	$nlist['resourcepriv'] = '3%';
	$nlist['descr'] = '100%';
	return $nlist;

}

static function initThisList($parent, $class)
{
	$res =  parent::baseinitThisList($parent, $class, 'permission');
	$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'permission', $res, true);
	$res = null;
	return $res;
}

static function createListAlist($parent, $class)
{
	$v = $parent->createShowAlist($alist);
	return $v['property'];
}

function display($var)
{
	if (!$this->$var) {
		return 'off';
	}

	return parent::display($var);
}
}


class Resource extends PermissionOrResource {

function perDisplay($var)
{
	if ($var === 'resourceusedper') {
		return array($this->resourcepriv, $this->resourceused, "");
	}
}
static $__desc = array("", "",  "resource_usage");
static $__desc_state = array("eS", "",  "s");
static $__desc_state_v_ok = array("", "",  "alright");
static $__desc_state_v_exceed = array("", "",  "exceeded" );
static $__desc_nname = array("", "",  "name");
static $__desc_descr = array("S", "",  "resource");
static $__desc_resourceused = array("S", "",  "used");
static $__desc_resourcepriv = array("S", "",  "max");
static $__desc_resourceusedper = array("Sp", "",  "graph");


static function initThisList($parent, $class)
{
	$res = parent::baseinitThisList($parent, $class, 'resource');

	//fOrcing creation of variables now itself.
	$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'resource', $res, true);

	$v = null;
	return $v;
}


}
