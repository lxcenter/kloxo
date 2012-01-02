<?php 

class licensecom_b extends Lxaclass {
static $__desc_nname =  array("", "",  "license");
static $__desc_lic_ipaddress =  array("n", "",  "Ipaddress");
static $__desc_clientaccount =  array("", "",  "Client Account");
static $__desc_password =  array("", "",  "Password");
}

class License extends Lxdb {
static $__desc =  array("", "",  "license");
static $__desc_nname =  array("", "",  "license");
static $__desc_licensecom_b =  array("", "",  "license");
static $__desc_lic_client_num_f =     array("","",  "number_of_clients");
static $__desc_lic_pserver_num_f =     array("","",  "number_of_servers");
static $__desc_lic_maindomain_num_f =     array("","",  "number_of_domains");
static $__desc_lic_vps_num_f =     array("","",  "number_of_vpses");
static $__desc_licensecom_b_s_password =  array("", "",  "Password");
static $__desc_lic_expiry_date_f =     array("","",  "expiry_date");
static $__desc_lic_live_support_f =     array("","",  "live_support");
static $__desc_lic_ipaddress_f =     array("","",  "ip_address");
static $__desc_lic_client_f =     array("","",  "client_support");
static $__desc_lic_node_num_f	 = array("", "",  "number_of_nodes");
static $__desc_license_upload_f =     array("F","",  "upload_new_license");
static $__desc_lic_livesupport_name_f =     array("F","",  "username");
static $__acdesc_update_login_info = array("", "",  "login_info");
static $__acdesc_update_license = array("", "",  "license_update");

static function initThisObjectRule($parent, $class, $name = null)
{
	return 'license';
}

function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	switch($subaction) 
	{
		case "license":
			{
				$lo = $login->getObject('license');
				$lic = $login->getObject('license')->licensecom_b;
				if (isset($lic->lic_ipaddress)) {
				$vlist['licensecom_b_s_lic_ipaddress'] = array('M', $lic->lic_ipaddress);
				}
				if (!isset($lic->lic_pserver_num)) {
					$vlist['__v_button'] = 'Get License From LxCenter';
				} else {
					$vlist['__v_button'] = 'Update License to unlimited';
				}
				if ($login->isAdmin()) {
					if (!isset($lic->lic_maindomain_num)) {
						$lic->lic_maindomain_num = $lic->lic_domain_num;
						$lo->setUpdateSubaction();
						$lo->write();
					}
					$vlist['lic_pserver_num_f'] = array('M', $lic->lic_pserver_num);
					if (isset($lic->lic_client_num)) {
					$vlist['lic_client_num_f'] = array('M', $lic->lic_client_num);
					}
					if ($sgbl->isKloxo()) {
						$vlist['lic_maindomain_num_f'] = array('M', $lic->lic_maindomain_num);
					} else {
						$vlist['lic_vps_num_f'] = array('M', $lic->lic_vps_num);
					}
				} else {
					$vlist['lic_node_num_f'] = array('M', $lic->node_num);
				}
				if (isset($lic->lic_livesupport_flag)) {
				$vlist['lic_live_support_f'] = array('M', $lic->lic_livesupport_flag);
				}
				if (isset($lic->lic_client)) {
				$vlist['lic_client_f'] = array('M', $lic->lic_client);
				}
				if (isset($lic->lic_livesupport_name)) {
				$vlist['lic_livesupport_name_f'] = array('M', $lic->lic_livesupport_name);
				}
				return $vlist;
			}

		case "login_info":
			{
				$vlist['licensecom_b_s_clientaccount'] = null;
				$vlist['licensecom_b_s_password'] = array('m', '****');
				return $vlist;
			}
	}
}

function createShowUpdateform()
{
	$uflist['license'] = null;
	return $uflist;
}
function createShowAlist(&$alist, $subaction = null)
{
	return $alist;
}

function updateLicense($param)
{
	self::doupdateLicense();
	return null;
}

static function doupdateLicense()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$rmt = new Remote();
	$rmt->program_name = $sgbl->__var_program_name;
	$var = base64_encode(serialize($rmt));
	if (!$login->isLteAdmin()) {
		throw new lxException ("not_admin", '');
	}

	$license = $login->getObject('license');
	$license->parent_clname = $login->getClName();


	$prilist = $login->getQuotaVariableList();

	foreach($prilist as $k => $v) {
		if (cse($k, "_flag")) {
			$login->priv->$k = 'On';
		} else if (cse($k, "_usage")) {
			$login->priv->$k = 'Unlimited';
		} else if (cse($k, "_num")) {
			$login->priv->$k = 'Unlimited';
		}
	}
	$login->setUpdateSubaction();
	$login->write();


	$lic = $license->licensecom_b;
	if ($sgbl->isKloxo()) {
		$def = array("maindomain_num" => "Unlimited", "domain_num" => 'Unlimited', "pserver_num" => "Unlimited", "client_num" => "Unlimited");
	} else {
		$def = array("vps_num" => 'Unlimited', "client_num" => "Unlimited");
	}
	$list = array("maindomain_num","domain_num","pserver_num");
	foreach($list as $l) {
		$licv = "lic_$l";
		$lic->$licv = $def[$l];
	}
	$license->setUpdateSubaction();
	$license->write();
}

function isSync() { return false ; }

}
?>
