<?php 


class dirprotect__iis extends lxDriverClass {


function genSalt ()
{
	$random = 0;
	$rand64 = "";
	$salt = "";

	$random=rand(); // Seeded via initialize()

	// Crypt(3) can only handle A-Z a-z ./

	$rand64= "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	$salt= substr($rand64,$random  %  64,1) . substr($rand64,($random/64) % 64,1);
	$salt=substr($salt,0,2); // Just in case

	return($salt);

}

function dbactionAdd()
{
	$Flag=0;
	//$iisid = $this->main->__var_iisid;
	$name = $this->main->nname;
	$path = $this->main->path;
    //$Dir = new COM("IIS://localhost/W3SVC/$iisid/ROOT/");
	//$Dir->AuthAnonymous = False;
	//$Dir->setInfo();
 	$domname = $this->main->getParentName();

	$dir = "__path_httpd_root/$domname/dirprotect/";
	$authuserfile = "$dir/{$this->main->getFileName()}";
	lxfile_mkdir($dir);
	$reauthstr = expand_real_root($authuserfile);
	$content = "AuthName {$this->main->authname}\n";
	$content .= "AuthUserFile $reauthstr\n";
	$content .= "Require valid-user\n";
	$path = "__path_httpd_root/$domname/httpdocs/www/{$this->main->path}";
	dprint(expand_real_root($path));

	lfile_put_contents("$path/.htlxaccess", $content);
	lxfile_mkdir($dir);

	//lfile_put_contents($path . "/.htpasswd",  $fstr);
	$this->createUser();


}

function createUser()
{
 	$domname = $this->main->getParentName();

	$dir = "__path_httpd_root/$domname/dirprotect/";
	$authuserfile = "$dir/{$this->main->getFileName()}";
	$fstr = null;


	foreach($this->main->diruser_a as $v) {
		$crypt = crypt($v->param, $this->genSalt());
		$fstr .= "$v->nname:$crypt";
		//$fstr .= $v->nname . ':' . $v->param . "\n";
	}

	lfile_put_contents($authuserfile,  $fstr);
}

function dbactionDelete()
{

}

function dbactionUpdate($subaction)
{
	$this->createUser();

}


}
