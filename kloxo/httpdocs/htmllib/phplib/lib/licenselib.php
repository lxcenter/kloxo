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
				$vlist['licensecom_b_s_lic_ipaddress'] = array('M', $lic->lic_ipaddress);
				if (!$lic->lic_pserver_num) {
					$vlist['__v_button'] = 'Get License From Lxlabs';
				} else {
					$vlist['__v_button'] = 'Update License From Lxlabs';
				}
				if ($login->isAdmin()) {
					if (!isset($lic->lic_maindomain_num)) {
						$lic->lic_maindomain_num = $lic->lic_domain_num;
						$lo->setUpdateSubaction();
						$lo->write();
					}
					$vlist['lic_pserver_num_f'] = array('M', $lic->lic_pserver_num);
					$vlist['lic_client_num_f'] = array('M', $lic->lic_client_num);
					if ($sgbl->isKloxo()) {
						$vlist['lic_maindomain_num_f'] = array('M', $lic->lic_maindomain_num);
					} else {
						$vlist['lic_vps_num_f'] = array('M', $lic->lic_vps_num);
					}
				} else {
					$vlist['lic_node_num_f'] = array('M', $lic->node_num);
				}
				$vlist['lic_live_support_f'] = array('M', $lic->lic_livesupport_flag);
				//$vlist['lic_ipaddress_f'] = array('M', $lic->lic_ipaddress);
				$vlist['lic_client_f'] = array('M', $lic->lic_client);
				$vlist['lic_livesupport_name_f'] = array('M', $lic->lic_livesupport_name);
				//$vlist['lic_current_f'] = array('t', lfile_get_contents('__path_program_etc/license.txt'));
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

	$lic = $login->getObject('license');
	//if (!$lic->licensecom_b->clientaccount) {
		//throw new lxException ("license_login_needed", '');
	//}
	//$rmt->login = $lic->licensecom_b->clientaccount;
	//$rmt->password = $lic->licensecom_b->password;

	//$rmt->ipaddress = $param['licensecom_b_s_lic_ipaddress'];

	//$res = send_to_some_http_server("localhost", "", "5558", $var);


	//$res = unserialize(base64_decode($res));
	$res = null;

	if (!$res) {
		$res = send_to_some_http_server("client.lxlabs.com", "", "5558", $var);
		$res = unserialize(base64_decode($res));
	}

	if (!$res) {
		send_mail_to_admin("Could_not_connect_to_license_server", "License has been reset to default");
		setLicenseTodefault();
		throw new lxException("could_not_connect_to_license_server", '');
	}

	if ($res->exception) {
		//$exc = new Exception("syncserver:$machine <br> " . $rmt->exception->getMessage());
		//$res->exception->syncserver = $machine;
		throw $res->exception;
		//throw $exc;
	}

	$val = trim($res->content);

	if (!$val) {
		throw new lxException("blank_license", '');
	}

	decodeAndStoreLicense($res->ipaddress, $val);

	// This is set so that the license alone feature - happens when the license expires - will properly redirect back to the original page. 
	//$gbl->__this_redirect = '/display.php?frm_action=show';
}

function isSync() { return false ; }


}
