<?php 

class MailingList__ezmlm extends lxDriverClass {


function dbactionAdd()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$headeradd = <<<HEAD
	Precedence: bulk
	X-No-Archive: yes
	List-Post: <mailto:<#l#>@<#h#>>
	List-Help: <mailto:<#l#>-help@<#h#>>
	List-Unsubscribe: <mailto:<#l#>-unsubscribe@<#h#>>
	List-Subscribe: <mailto:<#l#>-subscribe@<#h#>>

HEAD;

	$headerremove = <<<HEAD
	return-path
	return-receipt-to
	content-length
	precedence
	x-confirm-reading-to
	x-pmrqc
	list-subscribe
	list-unsubscribe
	list-help
	list-post

HEAD;


	list($listname, $dom) = explode("@", $this->main->nname);
	$headeradd .= "Reply-To: $listname@$dom\n";
	$headerremove .= "Reply-To\n";

	$mdom = strfrom($dom, "lists.");
	$sysuser = mmail__qmail::getUserGroup($mdom);

	if (!mmail__qmail::doesDomainExist($dom)) {
		$mdom = strfrom($dom, "lists.");
		$sysuser = mmail__qmail::getUserGroup($mdom);
		dprint("Domain $dom doesn't exist.. Creating.. with $userg\n");
		list($uid, $gid) = explode(":", $sysuser);
		$sys_cmd =  "__path_mail_root/bin/vadddomain";
		lxshell_return($sys_cmd, '-i', $uid, '-g', $gid, $dom, 'nothing');
		//lxshell_return($sys_cmd, $listdom, $password);
		$mailpath = mmail__qmail::getDir($dom);
		$qmailfile = "$mailpath/.qmail-default";
		//lxfile_unix_chown($qmailfile, mmail__qmail::getUserGroup($mdom));
	}

	$mailpath = mmail__qmail::getDir($dom);


	$dom_dir="$mailpath/$listname";
	if (isset($this->main->lang)) {
		$this->set_template($this->main->lang);
	} else {
		$ret["__syncv_lang"] = $this->main->__var_language;
		$this->set_template($this->main->__var_language);
	}
	lxuser_return($sysuser, "/usr/bin/ezmlm-make", "-ftx", "-5", $this->main->adminemail,  "$dom_dir/", "$mailpath/.qmail-$listname", $listname, $dom);
	$this->add_header("$dom_dir/headerremove", $headerremove);
	$this->add_header("$dom_dir/headeradd", $headeradd);
	if(!isset($this->main->text_trailer))
		$ret["__syncv_text_trailer"] =lfile_get_contents("$dom_dir/text/trailer") ;
	if(!isset($this->main->text_prefix))
		$ret["__syncv_text_prefix"] = lfile_get_contents("$dom_dir/prefix");
	if(!isset($this->main->max_msg_size) && !isset($this->main->min_msg_size))
		list($ret["__syncv_max_msg_size"],$ret["__syncv_min_msg_size"])=explode(':',lfile_get_contents("$dom_dir/msgsize"));
	if(!isset($this->main->text_mimeremove))
		$ret["__syncv_text_mimeremove"] = lfile_get_contents("$dom_dir/mimeremove");
	return $ret;

}


function dbactionDelete()
{
	global $gbl, $sgbl, $login, $ghtml;
	list($listname, $dom) = explode("@", $this->main->nname);
	if (!mmail__qmail::doesDomainExist($dom)) { return; }
	$mailpath = mmail__qmail::getDir($dom);
	if (!$listname) { return; }
	lxfile_rm_rec("$mailpath/$listname");
	lxfile_rm("$mailpath/.qmail-$listname");
	lxfile_rm("$mailpath/.qmail-$listname-default");
	lxfile_rm("$mailpath/.qmail-$listname-owner");
	lxfile_rm("$mailpath/.qmail-$listname-return-default");
}

function add_header($file, $head)
{
	list($listname, $dom) = explode("@", $this->main->nname);
	$sysuser = mmail__qmail::getUserGroup($dom);

	$cont = $head;
	lxuser_put_contents($sysuser, $file, $cont);

}

function set_template($lang)
{
	$base_template="/etc/ezmlm/ezmlmrc";
	if (!is_link ($base_template)) { // test if symlink exist, if not then make english version and symlink it
		rename($base_template, "$base_template.en");
		symlink ("$base_template.en", $base_template); } 
	if (!file_exists("$base_template.$lang")) { //test if language file exists, if not make one untranslated
				copy("$base_template.en","$base_template.$lang"); }
	unlink($base_template); //remove old symlink
	symlink("$base_template.$lang",$base_template); //create a new one to the present language
}

function configureThis()
{
	list($listname, $dom) = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($dom);
	$sysuser = mmail__qmail::getUserGroup($dom);
	$flags =	$this->main->isOn("post_members_only_flag") ?"u":"U";
	$flags .=	$this->main->isOn("post_moderated_flag") ?"m":"M";
	$flags .=	$this->main->isOn("post_moderator_only_flag")?"o":"O";
	$flags .=	$this->main->isOn("archived_flag") ?"a":"A";
	$flags .=	$this->main->isOn("archive_blocked_flag")?"b":"B";
	$flags .=	$this->main->isOn("archive_guarded_flag")?"g":"G";
	$flags .=	$this->main->isOn("digest_flag")?"d":"D";
	$flags .=	$this->main->isOn("jumpoff_flag")?"j":"J";
	$flags .=	$this->main->isOn("subscriberlist_flag")?"l":"L";
	$flags .=	$this->main->isOn("remote_admin_flag")?"r":"R";
	$flags .=	$this->main->isOn("subscription_mod_flag")?"s":"S";
	$flags .=	$this->main->isOn("edit_text_flag")?"n":"N";
	//some flags that are always on by default and that can be deactivated by deleting text from their respective fields. 
	//their default values will be stored to the database in dbactionAdd()
	$flags .=	"ftx"; // prefix, trailer and mime remove are always active
	lxfile_unix_chmod("$mailpath/$listname", "+t");
	$this->set_template($this->main->lang);
	$ret = lxuser_return($sysuser, "ezmlm-make", "-+$flags",  "$mailpath/$listname");
	lxuser_return($sysuser, "ezmlm-make", "-+5", $this->main->adminemail,  "$mailpath/$listname");
	lxuser_chmod($sysuser, "$mailpath/$listname", "-t");

}


function modsubsc()
{
	list($listname, $dom) = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($dom);
	$sysuser = mmail__qmail::getUserGroup($dom);
	foreach($this->main->__t_new_mailinglist_mod_a_list as $k) {
		lxuser_return($sysuser, "ezmlm-sub", "$mailpath/$listname/mod/", $k->nname);
	}

}

function modUnsubscribe()
{
	list($listname, $dom) = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($dom);
	$sysuser = mmail__qmail::getUserGroup($dom);
	foreach($this->main->__t_delete_mailinglist_mod_a_list as $k) {
		lxuser_return($sysuser, "ezmlm-unsub", "$mailpath/$listname/mod/", $k->nname);
	}

}

function savefile()
{
	list($listname, $dom) = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($dom);
	$sysuser = mmail__qmail::getUserGroup($dom);
	lxuser_put_contents($sysuser, "$mailpath/$listname/prefix", $this->main->text_prefix);
	lxuser_put_contents($sysuser, "$mailpath/$listname/text/trailer", $this->main->text_trailer);
	lxuser_put_contents($sysuser, "$mailpath/$listname/msgsize", $this->main->max_msg_size.":".$this->main->min_msg_size);
	lxuser_put_contents($sysuser, "$mailpath/$listname/mimeremove", $this->main->text_mimeremove);
}



function dbactionUpdate($subaction)
{

	switch($subaction) {
		case "update":
			$this->configureThis();
			break;

		case "add_mailinglist_mod_a":
			$this->modSubsc();
			break;

		case "delete_mailinglist_mod_a":
			$this->modUnsubscribe();
			break;

		case "editfile":
			$this->savefile();
			break;

	}

}


function do_backup()
{

	$var = explode('@', $this->main->nname);
	$mailpath = mmail__qmail::getDir($var[1]);
	$list = lxshell_output("/usr/bin/ezmlm-list", "$mailpath/{$var[0]}");
	$list = explode("\n", $list);
	$vd = createTempDir("/tmp", "ezmlm");
	$docf = "$vd/ezmlm-{$this->main->nname}.dump";
	lfile_put_contents($docf, serialize($list));
	return array($vd, array(basename($docf)));
}

function do_backup_cleanup($bc)
{
	lxfile_tmp_rm_rec($bc[0]);
}

function do_restore($docd)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$var = explode('@', $this->main->nname);
	$dom = $var[1];
	$mailpath = mmail__qmail::getDir($var[1]);
	$sysuser = mmail__qmail::getUserGroup($dom);
	$vd = createTempDir("/tmp", "ezmlmdump");

	$docf = "$vd/ezmlm-{$this->main->nname}.dump";
	lxshell_unzip_with_throw($sysuser, $vd, $docd);

	$cont = unserialize(lfile_get_contents($docf));
	lunlink($docf);


	foreach($cont as $l) {
		lxuser_return($sysuser, "/usr/bin/ezmlm-sub", "$mailpath/{$var[0]}/", $l);
	}
	lxfile_tmp_rm_rec($vd);

}



}


