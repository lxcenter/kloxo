<?php 

class client__sync extends lxDriverClass {

function dbactionDelete()
{
	lxfile_rm_rec("__path_client_root/{$this->main->nname}");
	lxfile_rm_rec("__path_customer_root/{$this->main->getPathFromName()}");
	lxshell_return("userdel", $this->main->username);
}

function dbactionAdd()
{
	global $gbl, $sgbl, $login, $ghtml; 
	lxfile_mkdir("__path_client_root/{$this->main->nname}");
	lxfile_mkdir("__path_client_root/{$this->main->nname}/__backup");
	lxfile_generic_chown("__path_client_root/{$this->main->nname}", "lxlabs");
	lxfile_generic_chown("__path_client_root/{$this->main->nname}/__backup", "lxlabs");

	// Issue #671 - Fixed backup-restore issue
	// disable checking if dir exists
/*
	if (lxfile_exists("__path_customer_root/{$this->main->getPathFromName()}")) {
		throw new lxException ("directory_already_exists", '', '');
	}
*/
	$ret = $this->createUser();
	$this->setupDefaultDomain();
	return $ret;

}

static function getFromRemote($user, $server, $filepass, $dt, $p)
{
	getFromRemote($server, $filepass, $dt, $p);
	lxfile_generic_chown_rec("$dt/$p", $user);
}

function createUser()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!$sgbl->isKloxo()) {
		return;
	}

	$password = $this->main->password;
	$cmd = "useradd"; 
	$shell = fix_disabled("--Disabled--", $sgbl->__var_noaccess_shell);
	$username = $this->main->getPathFromName();

	if (is_numeric($username[0])) {
		$username = "a$username";
	}

	$username = os_create_system_user($username, $password, $this->main->nname, $shell, "__path_customer_root/{$this->main->getPathFromName()}/");

	lxfile_unix_chown("__path_customer_root/{$this->main->getPathFromName()}", "{$username}:apache");
	lxfile_unix_chmod("__path_customer_root/{$this->main->getPathFromName()}", "750");

	$this->main->username = $username;

	$this->setQuota();
	$ret = array("__syncv_username" => $username);
	return $ret;
}

function setQuota()
{
	if (!is_unlimited($this->main->priv->totaldisk_usage)) {
		$disk = $this->main->priv->totaldisk_usage * 1024;
		os_set_quota($this->main->username, $disk);
	} else {
		os_set_quota($this->main->username, 0);
	}
}

function changeUserPass()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$sgbl->isKloxo()) {
		return;
	}
	$pass = $this->main->password;
	// Need to use single quotes.
	lxshell_return("usermod", "-p", $pass, $this->main->username);
}

function shellModify()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if_demo_throw_exception();
	$shell = fix_disabled($this->main->shell, $sgbl->__var_noaccess_shell);
	lxshell_return("usermod", "-s", $shell,  $this->main->nname);
	if ($this->main->isOn('disable_system_flag')) {
		lxshell_return("usermod", "-L", $this->main->nname);
	} else {
		lxshell_return("usermod", "-U", $this->main->nname);
	}
}

function ToggleStatus()
{
	if ($this->main->isOn('status')) {
		os_enable_user($this->main->username);
	} else {
		os_kill_process_user($this->main->username);
		os_disable_user($this->main->username);
	}
}

function dbactionUpdate($subaction)
{
	switch($subaction) {

		case "enable":
		case "disable":
		case "toggle_status":
			$this->ToggleStatus();
			break;

		case "password":
			$this->changeAdminPass();
			$this->changeUserPass();
			break;

		case "shell_access":
			$this->shellModify();
			break;

		case "change_totaldisk_usage":
			$this->setQuota();
			break;

		case "createuser":
			return $this->createuser();
			break;

		case "skeleton":
			// temporary only for admin - 6.1.7
		//	$file = "__path_client_root/{$this->main->nname}/skeleton.zip";
			$file = "../file/user-skeleton.zip";
			lxfile_mv($this->main->__skeletion_tmp, $file);
			lxfile_generic_chown($file, "lxlabs");
			break;

		case "default_domain":
			$this->setupDefaultDomain();
			break;

		case "ssh_authorized_keys":
			sshconfig::writeAuthorizedKeys($this->main->username, $this->main->ssh_authorized_keys_f);
			break;
	}
}

function setupDefaultDomain()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$sgbl->isKloxo()) {
		return;
	}

	if (!$this->main->default_domain) {
		return;
	}

	lunlink("/home/{$this->main->getPathFromName('nname')}/public_html");

	if ($this->main->isDisabled('default_domain')) {
		return;
	}
	dprint("linking {$this->main->__var_defdocroot}\n");

	lxfile_symlink("/home/{$this->main->getPathFromName()}/{$this->main->__var_defdocroot}/", "/home/{$this->main->getPathFromName()}/public_html");
}

function changeAdminPass()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$username = $sgbl->__var_program_name;
	if ($this->main->nname === 'admin') {
		dprint($this->main->realpass);
		$newp = client::createDbPass($this->main->realpass);
		$oldpass = lfile_get_contents("__path_admin_pass");
		$sql = new Sqlite(null, "client");
		//$sql->rawQuery("grant all on kloxo.* to kloxo@'localhost' identified by $newp");
		//$sql->rawQuery("grant all on kloxo.* to kloxo@'%' identified by $newp");
		//$return = $sql->setPassword($newp);
		//exec("mysqladmin -u $username -p$oldpass password $newp 2>&1", $out, $return);
		exec("echo 'set Password=Password(\"$newp\")' | mysql -u $username -p$oldpass 2>&1", $out, $return);
		if ($return) {
			$out = implode(" ", $out);
			log_log("admin_error", "mysql change password Failed $out");
			throw new lxException ("could_not_change_admin_pass", '', $out);
		}
		$return = lfile_put_contents("__path_admin_pass", $newp);
		if (!$return) {
			log_log("admin_error", "Admin pass change failed  $last_error");
			throw new lxException ("could_not_change_admin_pass", '', $last_error);
		}

	}
}

function do_backup()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$name = $this->main->getPathFromName('nname');
	$fullpath = "$sgbl->__path_customer_root/$name/";
	lxfile_mkdir($fullpath);
	$list = lscandir_without_dot_or_underscore($fullpath);
	return array($fullpath, $list);
}

function do_restore($docd)
{
	global $gbl, $sgbl, $login, $ghtml;
	$name = $this->main->getPathFromName('nname');
	$fullpath = "$sgbl->__path_customer_root/$name/";
	lxuser_mkdir($this->main->username, $fullpath);
	lxfile_generic_chown($docd, $this->main->username);
	lxuser_unzip_with_throw($this->main->username, $fullpath, $docd);
	lxfile_generic_chown($fullpath, "{$this->main->username}:apache");
	lxfile_generic_chmod($fullpath, "0750");
}

}
