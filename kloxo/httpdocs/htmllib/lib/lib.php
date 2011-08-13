<?php 
	
function getNumForString($name)
{
	$num = 0;
	for($i = 0; $i < strlen($name); $i++) {
		$num += ord($name[$i]) * $i;
	}
	$num = $num % 99999999;
	$num = intval($num);
	return $num;
}

function is_openvz()
{
	return lxfile_exists("/proc/user_beancounters");
}

function auto_update()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$gen = $login->getObject('general');
	if ($gen->generalmisc_b->isOn('autoupdate')) {
		dprint("Auto Updating\n");
		if (!checkIfLatest()) {
			exec_with_all_closed("$sgbl->__path_php_path ../bin/update.php");
		}
	} else {
        // Remove timezone warning
        date_default_timezone_set("UTC");		
        if ((date('d') == 10) && !checkIfLatest()) {
			$latest = getLatestVersion();
			$msg = "New Version $latest Available for $sgbl->__var_program_name";
			send_mail_to_admin($msg, $msg);
		}
	}
}

function print_head_image()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($sgbl->isBlackBackground()) { return; }
	if ($sgbl->isKloxo() && $gbl->c_session->ssl_param) {
		return;
	}
	if ($login->getSpecialObject('sp_specialplay')->isOn('show_thin_header')) {
		return;
	}

	?> <link href="/img/skin/kloxo/feather/default/feather.css" rel="stylesheet" type="text/css" /> <?php 
	print("<table class='bgtop3' width=100% cellpadding=0 cellspacing=0 style=\"background:url(/img/skin/kloxo/feather/default/invertfeather.jpg)\"> ");
	print("<tr  ><td width=100% id='td1' > </td> ");

	if ($login->getSpecialObject('sp_specialplay')->isOn('simple_skin')) {
		$v =  create_simpleObject(array('url' => "javascript:top.mainframe.logOut()", 'purl' => '&a=updateform&sa=logout', 'target' => null));
		print("<td valign=top>");
		print("<a href=javascript:top.mainframe.logOut()>Logout </a>");
		//$ghtml->print_div_button_on_header(null, true, 0, $v);
		print("</td>");
	}
	print("</tr>");
	print("<tr><td colspan=3 class='bg2'></td></tr>");
	print("</table> ");
}

function getIncrementedValueFromTable($table, $column)
{
	$sq = new Sqlite(null, $table);
	$res = $sq->rawQuery("select $column from $table order by ($column + 0) DESC limit 1");
	$value = $res[0][$column] + 1;
	return $value;
}

function http_is_self_ssl()
{
	return (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on'));

}

function core_installWithVersion($path, $file, $ver)
{
	global $sgbl;
	$prgm = $sgbl->__var_program_name;
	lxfile_mkdir("/var/cache/$prgm");
	if (!lxfile_real("/var/cache/$prgm/$file.$ver.zip")) {
		while (lxshell_return("unzip", "-t", "/var/cache/$prgm/$file.$ver.zip")) {
			system("cd /var/cache/$prgm/ ; rm -f $file*.zip; wget download.lxcenter.org/download/$file.$ver.zip");
		}
		system("cd $path ; unzip -oq /var/cache/$prgm/$file.$ver.zip");
	}
}

function download_thirdparty()
{
	global $sgbl;
	$prgm = $sgbl->__var_program_name;
	// Fixes #303 and #304
	$string = file_get_contents("http://download.lxcenter.org/download/thirdparty/$prgm-version.list");

	if ($string != "") {
		$string = trim($string);
		$string = str_replace("\n", "", $string);
		$string = str_replace("\r", "", $string);
		core_installWithVersion("/usr/local/lxlabs/$prgm/", "$prgm-thirdparty", $string);
		lxfile_unix_chmod("/usr/local/lxlabs/$prgm/httpdocs/thirdparty/phpMyAdmin/config.inc.php","0644");
	}
}


function get_other_driver($class, $driverapp)
{
	include "../file/driver/rhel.inc";
	$ret = null;
	if (is_array($driver[$class])) {
		foreach($driver[$class] as $l) {
			if ($l !== $driverapp) {
				$ret[] = $l;
			}
		}
	}
	return $ret;
}

function csainlist($string, $ssl) 
{
	foreach($ssl as $ss) {
		if (csa($string, $ss)) {
			return true;
		}
	}
	return false;
}

function file_put_between_comments($username, $stlist, $endlist, $startstring, $endstring, $file, $string)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (empty($string)) {
		dprint("ERROR: Function file_put_between_comments\nERROR: File ". $file . " has empty \$string\n");
		return;
	}
	$prgm = $sgbl->__var_program_name;

	$startcomment =  "###Please Don't edit these comments or the content in between. $prgm uses this to recognize the lines it writes to the the file. If the above line is corrupted, it may fail to recognize them, leading to multiple lines.";

	$outlist = null;
	$afterlist = null;
	$outstring = null;
	$afterend = false;
	if (lxfile_exists($file)) {
		$list = lfile_trim($file);
		$inside = false;
		foreach($list as $l) {
			if (csainlist($l, $stlist)) {
				$inside = true;
			}
			if (csainlist($l, $endlist)) {
				$inside = false;
				$afterend = true;
				continue;
			}

			if ($inside) {
				continue;
			}

			if ($afterend) {
				$afterlist[] = $l;
			} else {
				$outlist[] = $l;
			}
		}
	}

	if ($outlist) {
		$outstring = implode("\n", $outlist);
	}
	$afterstring = implode("\n", $afterlist);
	$outstring = "{$outstring}\n{$startstring}\n{$startcomment}\n{$string}\n{$endstring}\n$afterstring\n";

	lxuser_put_contents($username, $file, $outstring);
}

function lxfile_cp_if_not_exists($src, $dst)
{
	if (!lxfile_exists($dst)) {
		lxfile_cp($src, $dst);
	}
}

function db_get_value($table, $nname, $var)
{
	$sql = new Sqlite(null, $table);
	$row = $sql->getRowsWhere("nname = '$nname'", array($var));
	return $row[0][$var];
}

function monitor_load()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$val = os_getLoadAvg(true);
	
	$rmt = lfile_get_unserialize("../etc/data/loadmonitor");
	$threshold = 0;
	if ($rmt) { $threshold = $rmt->load_threshold; }
	if (!$threshold) { $threshold = 20; }

	if ($val < $threshold) { return; }

	dprint("load $val is greater than $threshold\n");

	$prgm = $sgbl->__var_program_name;

	$myname = trim(`hostname`);
	$time = date("Y-m-d H:m");
	$mess = "Load on $myname is $val at $time which is greater than $threshold\n";
	$mess .= "\n ------- Top ---------- \n";
	$topout = lxshell_output("top -n 1 -b");
	$mess .= $topout;
	$rmt = new Remote();
	$rmt->cmd = "sendemail";
	$rmt->subject = "Load Warning on $myname";
	$rmt->message = $mess;
	send_to_master($rmt);
}

function log_load()
{

	$mess = os_getLoadAvg();
	
	if (!is_string($mess)) {
		$mess = var_export($mess, true);
	}
	$mess = trim($mess);
	$rf = "__path_program_root/log/$file";
	if (WindowsOs()) {
		$endstr = "\r\n";
	} else {
		$endstr = "\n";
	}
	lfile_put_contents("/var/log/loadvg.log", time() . ' ' . @ date("H:i:M/d/Y") . ": $mess$endstr", FILE_APPEND);
}

function lxGetTimeFromString($line)
{	
	///2006-03-10 07:00:01
	$line = trimSpaces($line);
	$list = explode(" ", $line);
	return $list[0];
}

function recursively_get_file($dir, $file)
{
	if (lxfile_exists("$dir/$file")) {
		return "$dir/$file";
	}
	$list = lscandir_without_dot($dir);

	if (!$list) { return null; }

	foreach($list as $l) {
		if (lxfile_exists("$dir/$l/$file")) {
			return "$dir/$l/$file";
		}
	}
	return recursively_get_file("$dir/$l", $file);
}


function get_com_ob($obj)
{
	$ob = new Remote();
	$ob->com_object = $obj;
	return $ob;
}

function make_hidden_if_one($dlist)
{
	if (count($dlist) === 1) {
		return array('h', getFirstFromList($dlist));
	}

	return array('s', $dlist);
}

function get_quick_action_list($object)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$class = $object->getClass();

	$object->createShowAlist($alist);
	foreach($alist as $k => $v) {
		if (csb($k, "__title")) {
			$nalist[$k] = $v;
			continue;
		}
		if ($ghtml->is_special_url($v)) {
			continue;
		}
		if (csa($v, "a=update&")) {
			continue;
		}
		if ($object->isLogin()) {
			$nalist[$k] = $ghtml->getFullUrl($v);
		} else {
			$nalist[$k] = $ghtml->getFullUrl("j[class]=$class&j[nname]=__tmp_lx_name__&$v");
		}
	}
	return $nalist;
}

function get_favorite($class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$shortcut = $login->getVirtualList($class, $count);
	$back = $login->getSkinDir();
	$res = null;
	$ret = null;
	$iconpath = get_image_path() . "/button/";
	if ($shortcut) foreach($shortcut as $k => $h) {
		if (!is_object($h)) {
			continue;
		}

		if ($h->isSeparator()) {
			$res['ttype'] = 'separator';
			$ret[] = $res;
			continue;
		}


		$res['ttype'] = 'favorite';

		$url = base64_decode($h->url);
		// If the link is from kloxo, it shouldn't throw up a lot of errors. Needs to fix this properly..
		$ac_descr = @ $ghtml->getActionDetails($url, null, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);
		if ($sgbl->isHyperVM() && $h->vpsparent_clname) {
			$url = kloxo::generateKloxoUrl($h->vpsparent_clname, null, $url);
			$tag = "(l)";
		} else {
			//$url = $url;
			$tag = null;
		}

		if (isset($h->description)) {
			$str = $h->description;
		} else {
			$str = "$ac_descr[2] $__t_identity";
		}
		$fullstr = $str;
		if (strlen($str) > 18) {
			$str = substr($str, 0, 18);
			$str .= "..";
		}
		$str = htmlspecialchars($str);
		$target = "mainframe";
		if (is_object($h) && $h->isOn('external')) {
			$target = "_blank";
		}

		$vvar_list = array('_t_image' , 'url' , 'target' , '__t_identity' , 'ac_descr' , 'str' , 'tag', 'fullstr');
		foreach($vvar_list as $vvar) {
			$res[$vvar] = $$vvar;
		}
		$ret[] = $res;
		
	}
	return $ret;
}

function print_favorites()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$back = $login->getSkinDir();
	$list = get_favorite("ndskshortcut");

	$vvar_list = array('ttype', '_t_image' , 'url' , 'target' , '__t_identity' , 'ac_descr' , 'str' , 'tag');

	$res = null;
	foreach((array)$list as $l) {

		foreach($vvar_list as $vvar) {
			$$vvar = isset($l[$vvar]) ? $l[$vvar] : '';
		}
		if ($ttype == 'separator') {
			$res .= "<tr valign=top style=\"border-width:1; background:url($back/a.gif);\"> <td ></td> </tr>";
		}
		else {
			$res .= "<tr valign=top style=\"border-width:1; background:url($back/a.gif);\"> <td > <span title=\"$ac_descr[2] for $__t_identity\"> <img width=16 height=16 src=$_t_image> <a href=$url target=$target>  $str $tag</a></span></td> </tr>";
		}
	}
	return $res;
}

function print_quick_action($class)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$iconpath = get_image_path() . "/button/";
	if ($class === 'self') {
		$object = $login;
		$class = $login->getClass();
	} else {
		$list = $login->getVirtualList($class, $count);
		$object = getFirstFromList($list);
	}

	if (!$object) {
		return "No Object";
	}
	$namelist = get_namelist_from_objectlist($list);

	$alist = get_quick_action_list($object);
	foreach($alist as $a) {
		$ac_descr = $ghtml->getActionDetails($a, null, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);
	}
	$stylestr = "style=\"font-size: 10px\"";
	$res = null;
	$res .= " <tr style=\"background:#d6dff7\"> <td ><form name=quickaction method=$sgbl->method target=mainframe action=\"/htmllib/lbin/redirect.php\">";
	$desc = $ghtml->get_class_description($class);
	//$res .= "$desc[2] <br> ";
	if (!$object->isLogin()) {
		$res .= "<select $stylestr name=frm_redirectname>";
		foreach($namelist as $l){
			$pl = substr($l, 0, 26);
			$res .= '<option '.$stylestr.' value="'.$l.'" >'.$pl.'</option>';
		}
		$res .= "</select> </td> </tr>  ";
	}
	$res .= " <tr style=\"background:#d6dff7\"> <td ><select $stylestr name=frm_redirectaction>";
	foreach($alist as $k => $a) {
		if (csb($k, "__title")) {
			$res .= '<option value="" >------'.$a.'----</option>';
			continue;
		}
		$ac_descr = $ghtml->getActionDetails($a, null, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);
		$a = base64_encode($a);
		//$res .= "<option value=$a style='background-image: url($_t_image); background-repeat:no-repeat; left-padding: 35px; text-align:right'>  $ac_descr[2] </option>";
		$desc = substr($ac_descr[2], 0, 20);
		$res .= '<option '.$stylestr.' value="'.$a.'" >'.$desc.'</option>';
	}
	$res .= "</select> </td> </tr> ";
	$res .= "</form> <tr > <td align=right> <a href=javascript:quickaction.submit() > Go </a> </td> </tr> ";
	return $res;
}

function addtoEtcHost($request, $ip)
{
	//$iplist = os_get_allips();
	//$ip = $iplist[0];
	$comment = "added by kloxo dnsless preview";
	lfile_put_contents("/etc/hosts", "$ip $request #$comment\n", FILE_APPEND);
}

function fill_string($string, $num = 33)
{
	for($i = strlen($string); $i < $num; $i++) {
		$string .= ".";
	}
	return $string;
}

function removeFromEtcHost($request)
{
	$comment = "added by kloxo dnsless preview";
	$list = lfile_trim("/etc/hosts");
	$nlist = null;
	foreach($list as $l) {
		if (csa($l, "$request #$comment")) {
			continue;
		}
		$nlist[] = $l;
	}
	$out = implode("\n", $nlist);
	lfile_put_contents("/etc/hosts", "$out\n");
}

function find_php_version()
{
	global $global_dontlogshell;
	$global_dontlogshell = true;
	$ret = lxshell_output("rpm", "-q", "php");
	$ver =  substr($ret, strlen("php-"), 3);
	$global_dontlogshell = false;
	return $ver;
}


function createHtpasswordFile($object, $sdir, $list)
{
	$dir = "__path_httpd_root/{$object->main->getParentName()}/$sdir/";
	$loc = $object->main->directory;
	$file = get_file_from_path($loc);
	$dirfile = "$dir/$file";
	if (!lxfile_exists($dir)) {
		lxfile_mkdir($dir);
		lxfile_unix_chown($dir, $object->main->__var_username);
	}
	$fstr = null;
	foreach($list as $k => $p) {
		$cr = crypt($p);
		$fstr .= "$k:$cr\n";
	}
	dprint($fstr);

	lfile_write_content($dirfile,  $fstr, $object->main->__var_username);
	lxfile_unix_chmod($dirfile, "0755");
}

function get_file_from_path($path)
{
	return str_replace("/", "_", "slash_$path");
}
function get_total_files_in_directory($dir)
{
	dprint("$dir\n");
	$dir = expand_real_root($dir);
	$list = lscandir_without_dot($dir);
	return count($list);
}

function convert_favorite()
{
	lxshell_php("../bin/common/favoriteconvert.php");
}

function fix_meta_character($v)
{
	for ($i = 0; $i < strlen($v); $i++) {
		if (ord($v[$i]) > 128) {
			$nv[] = strtolower(urlencode($v[$i]));
		} else {
			$nv[] = $v[$i];
		}
	}
	return implode("", $nv);
}

function changeDriver($server, $class, $pgm)
{
	global $gbl, $sgbl, $login, $ghtml; 

	// Temporary hack. Somehow mysql doesnt' work in the backend.

	lxshell_return("__path_php_path", "../bin/common/setdriver.php", "--server=$server", "--class=$class", "--driver=$pgm");
	return;

	$server = $login->getFromList('pserver', $server);

	$os = $server->ostype;

	$dr = $server->getObject('driver');

	$v = "pg_$class";
	$dr->driver_b->$v = $pgm;

	$dr->setUpdateSubaction();

	$dr->write();

	print("Successfully changed Driver for $class to $pgm\n");
}

function changeDriverFunc($server, $class, $pgm)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$server = $login->getFromList('pserver', $server);

	$os = $server->ostype;

	include "../file/driver/$os.inc";
	dprintr($driver[$class]);
	if (is_array($driver[$class])) {
		if (!array_search_bool($pgm, $driver[$class])) {
			$str = implode(" ", $driver[$class]);
			print("The driver name isn't correct: Available drivers for $class: $str\n");
			return;
		}
	} else if ($driver[$class] !== $pgm) {
		print("The driver name isn't correct: Available driver for $class: {$driver[$class]}\n");
		return;
	}


	$dr = $server->getObject('driver');


	$v = "pg_$class";
	$dr->driver_b->$v = $pgm;

	$dr->setUpdateSubaction();

	$dr->write();

	print("Successfully changed Driver for $class on $server->nname to $pgm\n");
}

function slave_get_db_pass()
{
	$rmt = lfile_get_unserialize("../etc/slavedb/dbadmin");
	return $rmt->data['mysql']['dbpassword'];
}

function slave_get_driver($class)
{
	$rmt = lfile_get_unserialize("../etc/slavedb/driver");
	return $rmt->data[$class];
}

function PrepareRoundCubeDb()
{
	//  Related to issue #421

	global $gbl, $sgbl, $login, $ghtml;
	$pass = slave_get_db_pass();
	$user = "root";
	$host = "localhost";
	$link = mysql_connect($host, $user, $pass);
		if (!$link) {
		print("Mysql root password error\n");
		exit;
	}
	$pstring = null;
	if ($pass) {
		$pstring = "-p\"$pass\"";
	}

	$result = mysql_select_db('roundcubemail', $link);

	$roundcubefile = "/home/kloxo/httpd/webmail/roundcube/SQL/mysql.initial.sql";
	$content = lfile_get_contents($roundcubefile);
	$content = str_replace("ENGINE=INNODB", "", $content);
	lfile_put_contents($roundcubefile, $content);

	if (!$result) {
		print("Something went wrong, can not select RoundCube database!\n");
		print("Try to fix database...\n");
		$result = mysql_query("DROP DATABASE `roundcubemail`", $link);
		$result = mysql_query("CREATE DATABASE `roundcubemail`", $link);
		if (!$result) {
			print("There is REALY something very very wrong... Go to http://forum.lxcenter.org/ and report.\n\n");
			exit;
		}

		system("mysql -u root $pstring roundcubemail < /home/kloxo/httpd/webmail/roundcube/SQL/mysql.initial.sql");
		// -- don't use this update, because must be innodb
	//	system("mysql -u root $pstring roundcubemail < /home/kloxo/httpd/webmail/roundcube/SQL/mysql.update.sql");

		$result = mysql_select_db('roundcubemail', $link);
		if (!$result) {
			print("Something REALY went wrong, can not create RoundCube database!\n");
			exit;
		}
	}

	// --- issue #583 - solutions when database already exist but no tables
	$tbl = array();
	$tbl[0] = mysql_fetch_array(mysql_query('SHOW tables'));

	if (!$tbl[0]) {
		system("mysql -u root $pstring roundcubemail < /home/kloxo/httpd/webmail/roundcube/SQL/mysql.initial.sql");
		// -- don't use this update, because must be innodb
	//	system("mysql -u root $pstring roundcubemail < /home/kloxo/httpd/webmail/roundcube/SQL/mysql.update.sql");
	}

	system("chattr -i /home/kloxo/httpd/webmail/roundcube/config/db.inc.php");

	print("Generating password..\n");
	$pass = randomString(8);
//	dprint("Generated Pass " . $pass . "\n");
	$roundcubefileIN = "/usr/local/lxlabs/kloxo/file/webmail-chooser/db.inc.phps";
	$roundcubefileOUT = "/home/kloxo/httpd/webmail/roundcube/config/db.inc.php";
	$content = lfile_get_contents($roundcubefileIN);
	$content = str_replace("mysql://roundcube:pass", "mysql://roundcube:" . $pass, $content);
	system("chattr -i ".$roundcubefileOUT);
	lfile_put_contents($roundcubefileOUT, $content);

	$result = mysql_query("GRANT ALL ON roundcubemail.* TO roundcube@localhost IDENTIFIED BY '$pass'", $link);
	mysql_query("flush privileges", $link);
	if (!$result) {
		print("Could not grant privileges\nScript Abort.\n");
		exit;
	}
	print("RoundCube Database installed.\n");
	$pass = null;
	$pstring = null;

	//--- to make sure always 644
	system("chmod 644 /home/kloxo/httpd/webmail/roundcube/config/db.inc.php");
}

// --- new function with 'roundcube' style to replace 'old'
function PrepareHordeDb()
{
	global $gbl, $sgbl, $login, $ghtml;
	$pass = slave_get_db_pass();
	$user = "root";
	$host = "localhost";
	$link = mysql_connect($host, $user, $pass);
	if (!$link) {
		print("Mysql root password error\n");
		exit;
	}
	$pstring = null;
	if ($pass) {
		$pstring = "-p\"$pass\"";
	}

	$result = mysql_select_db('horde_groupware', $link);

	print("Fix database values in horde sql importfile\n");

	$hordefile = "/home/kloxo/httpd/webmail/horde/scripts/sql/groupware.mysql.sql";
	$content = lfile_get_contents($hordefile);
	$content = str_replace("CREATE DATABASE horde;", "CREATE DATABASE IF NOT EXISTS horde_groupware;", $content);
	lfile_put_contents($hordefile, $content);

	$content = lfile_get_contents($hordefile);
	$content = str_replace("USE horde;", "USE horde_groupware;", $content);
	lfile_put_contents($hordefile, $content);

	$content = lfile_get_contents($hordefile);
	$content = str_replace(") ENGINE = InnoDB;", ");", $content);
	lfile_put_contents($hordefile, $content);

	if (!$result) {
		print("Something went wrong, can not select Horde database!\n");
		print("Try to fix database...\n");
		$result = mysql_query("DROP DATABASE horde_groupware", $link);
		$result = mysql_query("CREATE DATABASE horde_groupware", $link);
		if (!$result) {
			print("There is REALY something very very wrong... Go to http://forum.lxcenter.org/ and report.\n\n");
			exit;
		}

		system("mysql -u root $pstring < /home/kloxo/httpd/webmail/horde/scripts/sql/groupware.mysql.sql");

		$result = mysql_select_db('horde_groupware', $link);
		if (!$result) {
			print("Something REALY went wrong, can not create Horde database!\n");
			exit;
		}
	}

	// --- issue #583 - solutions when database already exist but no tables
	$tbl = array();
	$tbl[0] = mysql_fetch_array(mysql_query('SHOW tables'));

	if (!$tbl[0]) {
		system("mysql -u root $pstring < /home/kloxo/httpd/webmail/horde/scripts/sql/groupware.mysql.sql");
	}

	lxfile_cp("/usr/local/lxlabs/kloxo/file/horde.config.phps", "/home/kloxo/httpd/webmail/horde/config/conf.php");
	system("chattr -i /home/kloxo/httpd/webmail/horde/config/conf.php");

	print("Generating password..\n");
	$pass = randomString(8);
//	dprint("Generated Pass " . $pass . "\n");
	print("Add Password to configfile\n");
	$content = lfile_get_contents("../file/horde.config.phps");
	$content = str_replace("__lx_horde_pass", $pass, $content);
	print("Remove system readonly attribute from configfile\n");
	system("chattr -i /home/kloxo/httpd/webmail/horde/config/conf.php");

	lfile_put_contents("/home/kloxo/httpd/webmail/horde/config/conf.php", $content);

	$result = mysql_query("GRANT ALL ON horde_groupware.* TO horde_groupware@localhost IDENTIFIED BY '$pass'", $link);
	mysql_query("flush privileges", $link);
	if (!$result) {
		print("Could not grant privileges\nScript Abort.\n");
		exit;
	}
	print("Horde Database installed.\n");
	$pass = null;
	$pstring = null;

	//--- to make sure always 644
	system("chmod 644 /home/kloxo/httpd/webmail/horde/config/conf.php");
}

function run_mail_to_ticket()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!$sgbl->is_this_master()) {
		return;
	}

	if (!$login) {
		initProgram('admin');
	}
	$ob = $login->getObject('ticketconfig');

	if (!$ob->isOn('mail_enable')) {
		return;
	}

	$portstring = null;
	$sslstring = null;
	if ($ob->isOn('mail_ssl_flag')) {
		$portstring = "and port 995";
		$sslstring = "with ssl";
	}

	$string = <<<FTC
set postmaster "postmaster"
set bouncemail
set properties ""
poll $ob->mail_server with proto POP3 $portstring user '$ob->mail_account' password '$ob->mail_password' is root here mda "lphp.exe ../bin/common/mailtoticket.php" options fetchall $sslstring
FTC;

	$tmp = lx_tmp_file("fetch");

	lfile_put_contents($tmp, $string);

	lxfile_generic_chown($tmp, "root:root");
	lxfile_generic_chmod($tmp, "0710");

	//system("pkill -f fetchmail");
	//sleep(10);
	exec_with_all_closed("fetchmail -d0 -e 15 -f $tmp; rm $tmp");
	//sleep(20);
	//lunlink($tmp);
}

function send_system_monitor_message_to_admin($prog)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$hst = trim(`hostname`);
	$dt = @ date('M-d h:i');
	$mess = "Host: $hst\nDate: $dt\n$prog\n\n\n";
	$rmt = new Remote();
	$rmt->cmd = "sendemail";
	$rmt->subject = "System Monitor on $hst";
	$rmt->message = $mess;
	send_to_master($rmt);

}

function check_if_port_on($port)
{
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	//socket_set_nonblock($socket);
	$ret = socket_connect($socket, "127.0.0.1", $port);
	socket_close($socket);
	if (!$ret) { return false; }
	return true;
}

function installAppPHP($var, $cmd)
{
	// TODO LxCenter: The created dir and file should be owned by the user
	global $gbl, $sgbl, $login, $ghtml; 
	$domain = $var['domain'];
	$appname = $var['appname'];

	lxfile_mkdir("/home/httpd/$domain/httpdocs/__installapplog");
	$i = 0;
	while(1) {
		$file = "/home/httpd/$domain/httpdocs/__installapplog/$appname$i.html";
		if (!lxfile_exists($file)) {
			break;
		}
		$i++;
	}

	if ($sgbl->dbg > 0) {
		//$cmd = "$cmd | elinks -no-home 1 -dump ";
		$cmd = "php $cmd | lynx -stdin -dump ";
	} else {
		$cmd = "php $cmd > $file";
	}
	system($cmd);
	dprint("\n*************************************************************************\n");

}


function validate_domain_name($name)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($name === 'lxlabs.com' || $name === 'lxcenter.org') {
		if (!$sgbl->isDebug()) {
			throw new lxException('lxlabs.com_or_lxcenter.org_cannot_be_added', 'nname');
		}
	}

	if (csb($name, "www.")) {
		throw new lxException('add_without_www', 'nname');
	}

	if(!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+(([a-z]{2,6})|(xn--[a-z0-9]{4,14}))$/i', $name)) {
		throw new lxException('invalid_domain_name', 'nname');
	}
	
	if (strlen($name) > 255) {
		throw new lxException('invalid_domain_name', 'nname');
	}
}

function execinstallappPhp($domain, $appname, $cmd)
{
	// TODO LxCenter: The created dir and file should be owned by the user
	global $gbl, $sgbl, $login, $ghtml; 
	lxfile_mkdir("/home/httpd/$domain/httpdocs/__installapplog");
	$i = 0;
	while(1) {
		$file = "/home/httpd/$domain/httpdocs/__installapplog/$appname$i.html";
		if (!lxfile_exists($file)) {
			break;
		}
		$i++;
	}

	if ($sgbl->dbg > 0) {
		//$cmd = "$cmd | elinks -no-home 1 -dump ";
		$cmd = "$cmd | lynx -stdin -dump ";
	} else {
		$cmd = "$cmd > $file";
	}
	system($cmd);
	dprint("\n*************************************************************************\n");
}

function update_self()
{
	global $gbl, $sgbl, $login, $ghtml; 
	exec_with_all_closed("$sgbl->__path_php_path ../bin/update.php");
}

function get_name_without_template($name)
{
	if (cse($name, "template")) {
		return strtil($name, "template");
	} else {
		return $name;
	}
}

function check_smtp_port()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($sgbl->is_this_slave()) { return; }
	$sq = new Sqlite(null, 'client');
	if (!check_if_port_on(25)) {
		$sq->rawQuery("update client set smtp_server_flag = 'off' where nname = 'admin'");
	} else {
		$sq->rawQuery("update client set smtp_server_flag = 'on' where nname = 'admin'");
	}

}

function getRealPidlist($arg)
{
	global $global_dontlogshell;
	$global_dontlogshell = true;

	$nlist = null;
	$list = lxshell_output("pgrep", "-f", $arg);

	$ret = lxshell_return("vzlist", "-a");

	$in_openvz_node = false;

	if (!$ret) {
		$in_openvz_node = true;
	}

	$list = explode("\n", $list);

	foreach($list as $l) {
		$l = trim($l);
		if (!$l) {
			continue;
		}
		if (posix_getpid() == $l) {
			continue;
		}

		if ($in_openvz_node) {
			$res = lxshell_output("sh", "../bin/common/misc/vzpid.sh", $l);
			$res = trim($res);
			if ($res != "0" && $res != "") {
				continue;
			}
		}
		$nlist[] = $l;
	}
	return $nlist;

}

function get_double_hex($i)
{
	$hex = dechex($i);
	if (strlen($hex) === 1) {
		$hex = "0$hex";
	}
	return $hex;
}


function merge_array_object_not_deleted($array, $object)
{
	foreach($array as $a) {
		if ($a['nname'] === $object->nname) {
			continue;
		}
		$ret[] = $a;
	}

	if ($object->isDeleted()) {
		return $ret;
	}

	foreach($object as $k => $v) {
		if (!is_object($v)) {
			$nl[$k] = $v;
		}
	}
	$ret[] = $nl;
	return $ret;
}

function call_with_flag($func)
{
	$file = "__path_program_etc/flag/$func.flg";
	if (lxfile_exists($file)) {
		return;
	}
	call_user_func($func);
	lxfile_touch($file);
}


function check_disable_admin($cgi_clientname)
{
	$sq = new Sqlite(null, 'general');
	$list = $sq->getRowsWhere("nname = 'admin'", array("disable_admin"));
	$val = $list[0]['disable_admin'];

	if ($cgi_clientname === 'admin' && $val === 'on') {
		return true;
	}
	return false;
}

function check_if_many_server()
{
	global $gbl, $sgbl, $login, $ghtml; 

	//if ($sgbl->isDebug()) { return true; }
	//$lic = $login->getObject('license');
	//$lic = $lic->licensecom_b;
	//return ($lic->lic_pserver_num > 1);

	$sql = new Sqlite(null, "pserver");
	$res = $sql->getTable(array('nname'));
	$rs = get_namelist_from_arraylist($res);
	if (count($rs) > 1) {
		return true;
	}
	return false;
}

function get_all_client()
{
	$sql = new Sqlite(null, "client");
	$res = $sql->getTable(array('nname'));
	$rs = get_namelist_from_arraylist($res);
	return $rs;
}

function get_all_pserver()
{
	$sql = new Sqlite(null, "pserver");
	$res = $sql->getTable(array('nname'));
	$rs = get_namelist_from_arraylist($res);
	return $rs;
}

function change_config($file, $var, $val)
{
	$list = lfile_trim($file);
	$match = false;
	foreach($list as &$__l) {
		if (csb($__l, "$var=") || csb($__l, "$var =")) {
			$__l = "$var=\"$val\"";
			$match = true;
		}
	}

	if (!$match) {
		$list[] = "$var=\"$val\"";
	}

	lfile_put_contents($file, implode("\n", $list));
}

function removeQuotes($val)
{
	$val = strfrom($val, '"');
	$val = strtil($val, '"');
	return $val;
}

function checkExistingUpdate()
{
	exit_if_another_instance_running();
}

function listFile($path)
{
	global $global_list_path;
	if (lis_dir($path)) {
		return;
	}
	$path = strfrom($path, "/usr/share/zoneinfo/");
	$path = trim($path, "/");
	if (ctype_lower($path[0])) {
		return;
	}
	$global_list_path[] = $path;
}

function execCom($ob, $func, $exception)
{
	try {
		$ret = $ob->$func();
	} catch (exception $e) {
		if (!$exception) {
			return null;
		}
		throw new lxException($exception, '');
	}
	return $ret;
}


function fix_vgname($vgname)
{
	if (csa($vgname, "lvm:")) { $vgname = strfrom($vgname, "lvm:"); }
	return $vgname;
}

function restart_mysql()
{
	exec_with_all_closed("service mysqld restart >/dev/null 2>&1");
}

function restart_service($service)
{
	exec_with_all_closed("service $service restart >/dev/null 2>&1");
}


function remove_old_serve_file()
{
	log_log("remove_oldfile", "Removing old files");
	$list = lscandir_without_dot("__path_serverfile/tmp");
	foreach($list as $l) {
		remove_if_older_than_a_day("__path_serverfile/tmp/$l");
	}
}

function fix_flag_variable($table, $flagvariable)
{
	$sq = new Sqlite(null, $table);
	$sq->rawQuery("update $table set $flagvariable = 'done' where $flagvariable = 'doing'");

}

function upload_file_to_db($dbtype, $dbhost, $dbuser, $dbpassword, $dbname, $file)
{
	mysql_upload_file_to_db($dbhost, $dbuser, $dbpassword, $dbname, $file);
}

function calculateRealTotal($inout)
{
	foreach($inout as $k => $v) {
		$sum = 0;
		foreach($v as $kk => $vv) {
			$sum += $vv;
		}

		$realtotalinout[$k] = $sum;
	}
	return $realtotalinout;
}

function mysql_upload_file_to_db($dbhost, $dbuser, $dbpassword, $dbname, $file)
{
	$rs = mysql_connect($dbhost, $dbuser, $dbpassword);

	if (!$rs) {
		throw new lxException('no_mysql_connection_while_uploading_file,', '');
	}

	mysql_select_db($dbname);

	$res = lfile_get_contents($file);

	$res = mysql_query($res);
	if (!$res) {
		throw new lxException('no_mysql_connection_while_uploading_file,', '');
	}
}

function testAllServersWithMessage()
{
	print("Testing All servers.... ");
	try {
		testAllServers();
	} catch (exception $e) {
		print("Connecting to these servers failed due to....\n");
		print_r($e->value);
		return false;
	}
	print("Done....\n");
	return true;
}


function testAllServers()
{
	$sq = new Sqlite(null, 'pserver');
	$res = $sq->getTable(array('nname'));
	$nlist = get_namelist_from_arraylist($res);

	$flist = null;
	foreach($nlist as $l) {
		try {
			rl_exec_get(null, $l, 'test_remote_func', null);
		} catch (exception $e) {
			$flist[$l] = $e->getMessage();
		}
	}

	if ($flist) {
		throw new lxException($e->getMessage(), '', $flist);
	}
}

function exec_with_all_closed($cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$string = null;
	log_shell("Closed Exec $sgbl->__path_program_root/cexe/closeallinput '$cmd' >/dev/null 2>&1 &");
	chmod("$sgbl->__path_program_root/cexe/closeallinput", 0755);
	exec("$sgbl->__path_program_root/cexe/closeallinput '$cmd' >/dev/null 2>&1 &");
}


function exec_with_all_closed_output($cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 
	chmod("$sgbl->__path_program_root/cexe/closeallinput", 0755);
	$res = shell_exec("$sgbl->__path_program_root/cexe/closeallinput '$cmd' 2>/dev/null");
	log_shell("Closed Exec output: $res :  $sgbl->__path_program_root/cexe/closeallinput '$cmd'");
	return trim($res);
}

// Convert Com to Php Array.
function convertCOMarray($array)
{
	foreach($array as $v) {
		$res[] = "$v";
	}
	return $res;
}

function mycount($olist)
{
	$i = 0;

	foreach($olist as $o) {
		$i++;
	}
	return $i;
}

function full_validate_ipaddress($ip, $variable = 'ipaddress')
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_dontlogshell;
	$global_dontlogshell = true;

	$gen = $login->getObject('general')->generalmisc_b;


	if (!validate_ipaddress($ip)) {
		throw new lxException("invalid_ipaddress", $variable);
	}

	$ret = lxshell_return("ping", "-n", "-c", "1", "-w", "5", $ip);

	if (!$ret) {
		throw new lxexception("some_other_host_uses_this_ip", $variable);
	}

	$global_dontlogshell = false;
}

function do_actionlog($login, $object, $action, $subaction)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($subaction === 'customermode') {
		return;
	}
	if (csb($subaction, 'boxpos')) {
		return;
	}

	if (!$object->is__table('domain') && !$object->is__table('client') && !$object->is__table('vps')) {
		return;
	}

	$d = microtime(true);
	$alog = new ActionLog(null, null, $d);
	$res['login'] = $login->nname;
	$res['loginclname'] = $login->getClName();
	$aux = $login->getAuxiliaryId();
	$res['auxiliary_id'] = $aux;
	$res['ipaddress'] = $gbl->c_session->ip_address;
	$res['class'] = $object->get__table();
	$res['objectname'] = $object->nname;
	$res['action'] = $action;
	$res['subaction'] = $subaction;
	$res['ddate'] = time();
	$alog->create($res);
	$alog->write();
}

function validate_email($email)
{
	$regexp = "/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@" .
		"((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i";
	if(!preg_match($regexp, $email)) {
		return false;
	}
	return true;
}

function validate_ipaddress_and_throw($ip, $variable)
{
	if (!validate_ipaddress($ip)) {
		throw new lxException("invalid_ipaddress", $variable);
	}
}

function validate_ipaddress($ip)
{
	$ind= explode(".",$ip);
	$d=0;
	$c=0;
	foreach($ind as $in) {
		$c++;
		if(is_numeric($in) && $in >= 0 && $in <= 255 ) {
			$d++;
		} else {
			return 0;
		}
	}
	if($c ===  4)   {
		if($d === 4) {
			return 1;
		} else {
			return 0;
		}
	} else  {
		return 0;
	}
}

function make_sure_directory_is_lxlabs($file)
{

}

function addToUtmp($ses, $dbaction)
{
	$nname = implode('_', array($ses->nname, $ses->parent_clname));
	$nname = str_replace(array(",", ":"), "_", $nname);
	$utmp = new Utmp(null, null, $nname);
	if ($dbaction === 'add') {
		$utmp->setFromObject($ses);
		$utmp->dbaction = 'add';
		$utmp->ssession_name = $ses->nname;
		$utmp->logouttime = 'Still Logged';
		$utmp->logoutreason = '-';
	} else {
		$utmp->get();
		$utmp->timeout = $ses->timeout;
		$utmp->setUpdateSubaction();
	}
	$utmp->write();
}

function getRealhostName($name)
{
	if ($name !== 'localhost') {
		return $name;
	}
	$sq = new Sqlite(null, 'pserver');
	$res = $sq->getRowsWhere("nname = '$name'", array('realhostname'));
	if (!$res[0]['realhostname']) {
		return 'localhost';
	}
	return $res[0]['realhostname'];
}

// This is mainly used for filserver. If the remote system is localhost, then return localhost itself, which means the whole thing is local. Otherwise return one of the ips that can be used to communicate with our server.
// The $v is actually the remote server that we are sending to.
function getOneIPForLocalhost($v)
{
	if (isLocalhost($v)) {
		return 'localhost';
	}
	if (is_secondary_master()) {
		$list = os_get_allips();
		$ip = getFirstFromList($list);
		return $ip;
	}
	return getFQDNforServer('localhost');
	
}

function getInternalNetworkIp($v)
{
	$sql = new Sqlite(null, "pserver");

	$server = $sql->rawQuery("select * from pserver where nname = '$v'");

	$servername = trim($server[0]['internalnetworkip']);

	if ($servername) {
		return $servername;
	}
	return getFQDNforServer($v);
}

function get_form_variable_name($descr)
{
	return getNthToken($descr, 1);
}

function is_disabled($var)
{
	return ($var === '--Disabled--');
}

function is_disabled_or_null($var)
{
	return (!$var || $var === '--Disabled--');
}

function getFQDNforServer($v)
{
	$sql = new Sqlite(null, "pserver");

	$server = $sql->rawQuery("select * from pserver where nname = '$v'");

	$servername = trim($server[0]['realhostname']);
	if ($servername) {
		return $servername;
	}

	return getOneIPForServer($v);
}

function getOneIPForServer($v)
{
	$sql = new Sqlite(null, "pserver");
	$ipaddr = $sql->rawQuery("select * from ipaddress where syncserver = '$v'");

	foreach($ipaddr as $ip) {
		if (!csb($ip['ipaddr'], "127") && !csb($ip['ipaddr'], "172") && !csb($ip['ipaddr'], "192.168")) {
			return $ip['ipaddr'];
		}
	}
	// Try once more if no non-local ips were found...
	foreach($ipaddr as $ip) {
		if (!csb($ip['ipaddr'], "127")) {
			return $ip['ipaddr'];
		}
	}
	return null;
}

function zip_to_fileserv($dir, $fillist)
{
	$file = do_zip_to_fileserv('zip', array($dir, $fillist));
	return cp_fileserv($file);
}

function tar_to_fileserv($dir, $fillist)
{
	$file = do_zip_to_fileserv('tar', array($dir, $fillist));
	return cp_fileserv($file);
}

function tgz_to_fileserv($dir, $fillist)
{
	$file = do_zip_to_fileserv('tgz', array($dir, $fillist));
	return cp_fileserv($file);
}

function get_admin_license_var()
{
	$list = get_license_resource();
	foreach($list as &$__l) {
		$__l = "used_q_$__l";
	}
	$sq = new Sqlite(null, 'client');
	$res = $sq->getRowsWhere("nname = 'admin'", $list);
	return $res[0];
}

function get_license_resource()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($sgbl->isKloxo()) {
		return array("maindomain_num");
	} else {
		return array("vps_num");
	}
}

function cp_fileserv_list($root, $list)
{
	foreach($list as $l) {
		$fp = "$root/$l";
		$res[$fp] = cp_fileserv($fp);
	}
	return $res;
}

function cp_fileserv($file)
{
	lxfile_mkdir("__path_serverfile");
	lxfile_generic_chown("__path_serverfile", "lxlabs:lxlabs");
	$file = expand_real_root($file);
	dprint("Fileserv copying file $file\n");
	if (is_dir($file)) {
		$list = lscandir_without_dot($file);
		$res =  tar_to_fileserv($file, $list);
		$res['type'] = "dir";
		return $res;
	} else {
		$res['type'] = 'file';
	}

	$basebase = basename($file);
	$base = basename(ltempnam("__path_serverfile", $basebase));
	$pass = md5($file . time());
	$ar = array('filename' => $file, 'password' => $pass);
	lfile_put_serialize("__path_serverfile/$base", $ar);
	lxfile_generic_chown("__path_serverfile/$base", "lxlabs");
	$res['file'] = $base;
	$res['pass'] = $pass;
	//$stat = llstat("__path_serverfile/$base");
	$res['size'] = lxfile_size($file);
	return $res;
}

function do_zip_to_fileserv($type, $arg)
{
	lxfile_mkdir("__path_serverfile/tmp");
	lxfile_unix_chown_rec("__path_serverfile", "lxlabs");
	
	$basebase = basename($arg[0]);

	$base = basename(ltempnam("__path_serverfile/tmp", $basebase));

	// Create the pass file now itself so that it isn't unwittingly created again.

	if ($type === 'zip') {
		$vd = $arg[0];
		$list = $arg[1];
		dprint("zipping $vd: " . implode(" ", $list) . " \n");
		$ret = lxshell_zip($vd, "__path_serverfile/tmp/$base.tmp", $list);
		lrename("__path_serverfile/tmp/$base.tmp", "__path_serverfile/tmp/$base");
	} else if ($type === 'tgz') {
		$vd = $arg[0];
		$list = $arg[1];
		dprint("tarring $vd: " . implode(" ", $list) . " \n");
		$ret = lxshell_tgz($vd, "__path_serverfile/tmp/$base.tmp", $list);
		lrename("__path_serverfile/tmp/$base.tmp", "__path_serverfile/tmp/$base");
	} else if ($type === 'tar') {
		$vd = $arg[0];
		$list = $arg[1];
		dprint("tarring $vd: " . implode(" ", $list) . " \n");
		$ret = lxshell_tar($vd, "__path_serverfile/tmp/$base.tmp", $list);
		lrename("__path_serverfile/tmp/$base.tmp", "__path_serverfile/tmp/$base");
	}

	if ($ret) {
		throw new lxException("could_not_zip_dir", '', $vd);
	}

	return "__path_serverfile/tmp/$base";
}


function fileserv_unlink_if_tmp($file)
{
	$base = dirname($file);
	if (expand_real_root($base) === expand_real_root("__path_serverfile/tmp")) {
		log_log("servfile", "Deleting tmp servfile $file");
		lunlink($file);
	}
}



function getFromRemote($server, $filepass, $dt, $p)
{
	$bp = basename($p);
	if ($filepass['type'] === 'dir') {
		$tfile = lx_tmp_file("__path_tmp", "lx_$bp");
		getFromFileserv($server, $filepass, $tfile);
		lxfile_mkdir("$dt/$bp");
		lxshell_unzip_with_throw("$dt/$bp", $tfile);
		lunlink($tfile);
	} else {
		getFromFileserv($server, $filepass, "$dt/$bp");
	}
}

function exit_if_not_system_user()
{
	if (!os_isSelfSystemUser()) {
		print("Need to be system user\n");
		exit;
	}
}

function getFromFileserv($serv, $filepass, $copyto)
{
	global $gbl, $sgbl, $login, $ghtml; 

	doRealGetFromFileServ("file", $serv, $filepass, $copyto);
}


function printFromFileServ($serv, $filepass)
{
	doRealGetFromFileServ("fileprint", $serv, $filepass);
}
 
function doRealGetFromFileServ($cmd, $serv, $filepass, $copyto = null)
{
	$file = $filepass['file'];
	$pass = $filepass['pass'];
	$size = $filepass['size'];
	$base = basename($file);

	if ($serv === 'localhost') {
		$array = lfile_get_unserialize("__path_serverfile/$base");
		$realfile = $array['filename'];
		log_log("servfile", "getting local file $realfile");
		if (lxfile_exists($realfile) && lis_readable($realfile)) {
			lunlink("__path_serverfile/$base");
			if ($cmd === 'fileprint') {
				slow_print($realfile);
			} else {
				lxfile_mkdir(dirname($copyto));
				lxfile_cp($realfile, $copyto);
			}
			fileserv_unlink_if_tmp($realfile);
			return;
		}
		if (os_isSelfSystemUser()) {
			log_log("servfile", "is System User, but can't access $realfile returning");
			//return;
		} else {
			log_log("servfile", "is Not system user, can't access so will get $realfile through backend");
		}

	}

	$fd = null;
	if ($copyto) {
		lxfile_mkdir(dirname($copyto));
		$fd = lfopen($copyto, "wb");
		if (!$fd) {
			log_log("servfile", "Could not write to $copyto... Returning.");
			return;
		}
		lxfile_generic_chmod($copyto, "0700");
	}

	doGetOrPrintFromFileServ($serv, $filepass, $cmd, $fd);

	if ($fd) { fclose($fd); }
}

function doGetOrPrintFromFileServ($serv, $filepass, $type, $fd)
{

	$file = $filepass['file'];
	$pass = $filepass['pass'];
	$size = $filepass['size'];

	$info = new Remote;
	$info->password = $pass;
	$info->filename = $file;
	log_log("servfile", "Start Getting $serv $type $file $size");

	$val = base64_encode(serialize($info));
	$string = "__file::$val";

	$totalsize = send_to_some_stream_server($type, $size, $serv, $string, $fd);

	log_log("servfile", "Got $serv $type $file $size (Totalsize willbe +1) $totalsize");
}

function trimSpaces($val)
{
	$val = trim($val);
	$val = preg_replace("/\s+/", " ", $val);

	return $val;
}


function execRrdTraffic($filename, $tot, $inc, $out)
{
	global $global_dontlogshell;
	global $global_shell_error, $global_shell_ret, $global_shell_out;
	$global_dontlogshell = true;
	$file = "__path_program_root/data/traffic/$filename.rrd";
	lxfile_mkdir("__path_program_root/data/traffic");
	if (!lxfile_exists($file)) {
		lxshell_return("rrdtool", 'create', $file, 'DS:total:ABSOLUTE:800:-1125000000:1125000000', 'DS:incoming:ABSOLUTE:800:-1125000000:1125000000', 'DS:outgoing:ABSOLUTE:800:-1125000000:1125000000', 'RRA:AVERAGE:0.5:1:600', 'RRA:AVERAGE:0.5:6:700', 'RRA:AVERAGE:0.5:24:775', 'RRA:AVERAGE:0.5:288:797');
	}
	lxshell_return("rrdtool", "update", $file, "N:$tot:$inc:$out");
}


function set_login_skin_to_feather()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$sgbl->isKloxo()) { return; }
	$obj = $login->getObject('sp_specialplay');
	$obj->specialplay_b->skin_name = 'feather';
	$obj->specialplay_b->skin_color = 'default';
	$obj->setUpdateSubaction();
	$obj->write();

	$obj = $login->getObject('sp_childspecialplay');
	$obj->specialplay_b->skin_name = 'feather';
	$obj->specialplay_b->skin_color = 'default';
	$obj->setUpdateSubaction();
	$obj->write();
}

function redirect_to_https()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($sgbl->is_this_slave()) { print("This is a Slave Server\n"); exit; }

	include_once "htmllib/phplib/lib/generallib.php";

	$port = db_get_value("general", "admin", "ser_portconfig_b"); 
	$port = unserialize(base64_decode($port));

	if (http_is_self_ssl()){
		return;
	}
	if (!is_object($port)) {
		return;
	}

	if (!$port->isOn('redirectnonssl_flag')) {
		return;
	}

	$sslport = $port->sslport;

	if (!$sslport) { $sslport = $sgbl->__var_prog_ssl_port; }

	$host = $_SERVER['HTTP_HOST'];

	if (csa($host, ":")) {
		$ip = strtilfirst($host, ":");
	} else {
		$ip = $host;
	}
	header("Location: https://$ip:$sslport");
	exit;
}

function execRrdSingle($name, $func, $filename, $tot)
{
	global $global_dontlogshell;
	global $global_shell_error, $global_shell_ret, $global_shell_out;
	$global_dontlogshell = true;
	$tot = round($tot);
	$file = "__path_program_root/data/$name/$filename.rrd";
	lxfile_mkdir("__path_program_root/data/$name");
	if (!lxfile_exists($file)) {
		lxshell_return("rrdtool", 'create', $file, "DS:$name:$func:800:0:999999999999", 'RRA:AVERAGE:0.5:1:600', 'RRA:AVERAGE:0.5:6:700', 'RRA:AVERAGE:0.5:24:775', 'RRA:AVERAGE:0.5:288:797');
	}
	lxshell_return("rrdtool", "update", $file, "N:$tot");
}


function get_num_for_month($month)
{
	$list = array("", "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec");
	return array_search(strtolower($month), $list);
}

function rrd_graph_single($type, $file, $time)
{
	global $global_dontlogshell;
	global $global_shell_error, $global_shell_ret, $global_shell_out;
	$global_dontlogshell = true;
	$dir = strtilfirst($type, " ");
	$file = "__path_program_root/data/$dir/$file.rrd";
	$file = expand_real_root($file);
	$graphfile = ltempnam("/tmp", "lx_graph");

	if (!lxfile_exists($file)) {
		throw new lxexception("no_graph_data");
	}

	if ($time >= 7 * 24 * 3600) {
		$grid = 'HOUR:12:DAY:2:WEEK:8:0:%X';
	} else if ($time >= 24 * 3600) {
		$grid = 'MINUTE:30:HOUR:2:HOUR:8:0:%X';
	} else {
		$grid = 'MINUTE:3:MINUTE:30:HOUR:1:0:%X';
	}

	$ret = lxshell_return('rrdtool', 'graph', $graphfile, '--start', "-$time", '-w', '600', '-h', '200', '--x-grid', $grid, "--vertical-label=$type", "DEF:dss1=$file:$dir:AVERAGE", "LINE1:dss1#FF0000:$dir\\r");

	if ($ret) {
		throw new lxexception("could_not_get_graph_data", '', $global_shell_error);
	}

	$content = lfile_get_contents($graphfile);
	lunlink($graphfile);
	$global_dontlogshell = false;
	return $content;
}

function rrd_graph_vps($type, $file, $time)
{
	global $global_dontlogshell;
	global $global_shell_error, $global_shell_ret, $global_shell_out;
	$global_dontlogshell = true;
	$file = "__path_program_root/data/$type/$file";
	$file = expand_real_root($file);
	$graphfile = ltempnam("/tmp", "lx_graph");

	if (!lxfile_exists($file)) {
		throw new lxexception("no_traffic_data");
	}

	if ($time >= 7 * 24 * 3600) {
		$grid = 'HOUR:12:DAY:2:WEEK:8:0:%X';
	} else if ($time >= 24 * 3600) {
		$grid = 'MINUTE:30:HOUR:2:HOUR:8:0:%X';
	} else {
		$grid = 'MINUTE:3:MINUTE:30:HOUR:1:0:%X';
	}

	switch($type) {
		case "traffic":
			$ret = lxshell_return('rrdtool', 'graph', $graphfile, '--start', "-$time", '-w', '600', '-h', '200', '--x-grid', $grid, '--vertical-label=Bytes/s', "DEF:dss0=$file:total:AVERAGE", "DEF:dss1=$file:incoming:AVERAGE", "DEF:dss2=$file:outgoing:AVERAGE", 'LINE1:dss0#00FF00:Total traffic', 'LINE1:dss1#FF0000:In traffic\\r', 'LINE1:dss2#0000FF:Out traffic\\r');
			break;

		default:
			$ret = lxshell_return('rrdtool', 'graph', $graphfile, '--start', "-$time", '-w', '600', '-h', '200', '--x-grid', $grid, "--vertical-label=$type", "DEF:dss1=$file:$type:AVERAGE", "LINE1:dss1#FF0000:$type\\r");
			break;
	}

	if ($ret) {
		throw new lxexception("couldnt_get_traffic_data", '', $global_shell_error);
	}

	$content = lfile_get_contents($graphfile);
	lunlink($graphfile);
	$global_dontlogshell = false;
	return $content;
}

function rrd_graph_server($type, $list, $time)
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_dontlogshell;
	global $global_shell_error;
	$global_dontlogshell = true;
	$graphfile = ltempnam("/tmp", "lx_graph");

	$color = array( "000000", "b54f6f", "00bb00", "a0ad00", "0090bf", "56a656", "00bbbf", "bfbfbf", "458325", "f04050", "a0b2c5", "cf0f00", "a070ad", "cf8085", "af93af", "90bb9f", "00d500", "00ff00", "aaffaa", "00ffff", "aa00ff", "ffff00", "aaff00", "faff00", "0aff00", "6aff00", "eaffa0", "abff0a", "afffaa", "deab3d", "333333", "894367", "234567", "fbdead", "fadec1", "fa3d9c", "f54398", "f278d3", "f512d3", "43f3f9", "f643f9");

	if ($time >= 7 * 24 * 3600) {
		$grid = 'HOUR:12:DAY:2:WEEK:8:0:%X';
	} else if ($time >= 24 * 3600) {
		$grid = 'MINUTE:30:HOUR:2:HOUR:8:0:%X';
	} else {
		$grid = 'MINUTE:3:MINUTE:30:HOUR:1:0:%X';
	}

	switch($type) {
		case "traffic":

			$i = 0;
			foreach($list as $k => $file) {
				$i++;
				$fullpath = "$sgbl->__path_program_root/data/$type/$file.rrd";
				if (!lxfile_exists($fullpath)) {
					continue;
				}
				$arg[] = "DEF:dss$i=$fullpath:total:AVERAGE";
				if (isset($color[$i])) {
					$arg[] = "LINE1:dss$i#$color[$i]:$k";
				} else {
					$arg[] = "LINE1:dss$i#000000:$k";
				}

			}
			$arglist = array('rrdtool', 'graph', $graphfile, '--start', "-$time", '-w', '600', '-h', '200', '--x-grid', $grid, '--vertical-label=Bytes/s');

			$arglist = lx_array_merge(array($arglist, $arg));

			$ret = call_user_func_array("lxshell_return", $arglist);
			break;

		default:
			$i = 0;
			foreach($list as $k => $file) {
				$i++;
				$fullpath = "$sgbl->__path_program_root/data/$type/$file.rrd";
				$arg[] = "DEF:dss$i=$fullpath:$type:AVERAGE";

				if (isset($color[$i])) {
					$arg[] = "LINE1:dss$i#$color[$i]:$k";
				} else {
					$arg[] = "LINE1:dss$i#000000:$k";
				}

			}
			$arglist = array('rrdtool', 'graph', $graphfile, '--start', "-$time", '-w', '600', '-h', '200', '--x-grid', $grid, "--vertical-label=$type");
			$arglist = lx_array_merge(array($arglist, $arg));
			$ret = call_user_func_array("lxshell_return", $arglist);
			break;
	}

	if ($ret) {
		throw new lxexception("graph_generation_failed", null, $global_shell_error);
	}

	$content = lfile_get_contents($graphfile);
	lunlink($graphfile);
	$global_dontlogshell = false;
	return $content;
}


function slow_print($file)
{
	$fp = lfopen($file, "rb");

	while(!feof($fp)) {
		print(fread($fp, 8092));
		flush();
		//usleep(600 * 1000);
		//sleep(1);
	}
	fclose($fp);
}

function createTempDir($dir, $name)
{
	$dir = expand_real_root($dir);
	$vd = tempnam($dir, $name);
	if (!$vd) {
		throw new lxException('could_not_create_tmp_dir', '');
	}
	unlink($vd);
	mkdir($vd);
	lxfile_generic_chmod($vd, "0700");
	return $vd;
}

function getObjectFromFileWithThrow($file)
{
	$rem = unserialize(lfile_get_contents($file));

	if (!$rem) {
		throw new lxException('corrupted_file', 'dbname', '');
	}
	return $rem;
}


function checkIfVariablesSetOr($p, &$param, $v, $list)
{
	foreach($list as $l) {
		if (isset($p[$l]) && $p[$l]) {
			$param[$v] = $p[$l];
			return;
		}
	}

	throw new lxException ("need_{$list[0]}", '');
}

function checkIfVariablesSet($p, $list)
{
	foreach($list as $l) {
		if (!isset($p[$l]) || !$p[$l]) {
			$n = str_replace("-", "_", $l);
			throw new lxException("need_{$n}", '', $l);
		}
	}
}

function get_variable($list)
{
	$vlist = null;
	foreach($list as $k => $v) {
		if (csb($k, "v-")) {
			$vlist[strfrom($k, "v-")] = $v;
		}
	}
	return $vlist;
}


function parse_opt($argv)
{
	unset($argv[0]);
	if (!$argv) {
		return  null;
	}
	foreach($argv as $v) {
		if (!csb($v, "--")) {
			$ret['final'] = $v;
			continue;
		}
		$v = strfrom($v, "--");
		if (csa($v, "=")) {
			$opt = explode("=", $v);
			$ret[$opt[0]] = $opt[1];
		} else {
			$ret[$v] = $v;
		}
	}
	return $ret;
}

function fix_rhn_sources_file()
{
	$os = findOperatingSystem('pointversion');
	$list = lfile("/etc/sysconfig/rhn/sources");
	foreach($list as $k => $l) {
		$l = trim($l);

		if (!$l) {
			continue;
		}
		if (csb($l, "yum lxcenter")) {
			continue;
		}
		$outlist[$k] = $l;
	}

	$outlist[] = "\n";
	$outlist[] = "yum lxcenter-updates http://download.lxcenter.org/download/update/$os/\$ARCH/";
	$outlist[] = "yum lxcenter-lxupdates http://download.lxcenter.org/download/update/lxgeneral/";

	lfile_put_contents("/etc/sysconfig/rhn/sources", implode("\n", $outlist) . "\n");
	$cont = lfile_get_contents( "__path_program_htmlbase/htmllib/filecore/lxcenter.repo.template");
	
	$cont = str_replace("%distro%", $os, $cont);
	lfile_put_contents("/etc/yum.repos.d/lxcenter.repo", $cont);
}


function mkdir_ifnotExist($name)
{
}

function opt_get_single_flag($opt, $var)
{
	$ret = false;
	if (isset($opt[$var]) && $opt[$var] === $var) {
		$ret = true;
	}
	return $ret;
}


function opt_get_default_or_set($opt, $val, $def)
{
	if (!isset($opt[$val])) {
		return $def;
	} else {
		return $opt[$val];
	}
}

function is_running_secondary()
{
	return lxfile_exists("../etc/running_secondary");
}

function exit_if_running_secondary()
{
	if (is_running_secondary()) {
		print("This is Running secondary\n");
		exit;
	}
}

function is_secondary_master()
{
	return lxfile_exists("../etc/secondary_master");
}

function exit_if_secondary_master()
{
	if (is_secondary_master()) {
		print("This is secondary Master\n");
		exit;
	}
}
function exit_if_another_instance_running()
{
	if (lx_core_lock()) {
		print("Another Copy of the same program is currently Running on pid\n");
		exit;
	}
}


function lx_core_lock($file = null)
{
	global $argv;
	$prog = basename($argv[0]);

	// This is a hack.. If we can't get the arg, then that means we are in the cgi mode, and that means our process name is display.php.
	if (!$prog) { $prog = "display.php"; }
	lxfile_mkdir("../pid");

	if (!$file) {
		$file = "$prog.pid";
	} else {
		$file = basename($file);
	}

	$pidfile = "__path_program_root/pid/$file";
	$pid = null;
	if (lxfile_exists($pidfile)) {
		$pid = lfile_get_contents($pidfile);
	}
	dprint("PID#:  ".$pid."\n");
	if (!$pid) {
		dprint("\n$prog:$file\nNo pid file $pidfile detected..\n");
		lfile_put_contents($pidfile, os_getpid());
		return false;
	}

	$pid = trim($pid);
	$name = os_get_commandname($pid);

	if ($name) {
		$name = basename($name);
	}

	if (!$name || $name !== $prog) {
		if (!$name) {
			dprint("\n$prog:$file\nStale Lock file detected.\n$pidfile\nRemoving it...\n ");
		} else {
			dprint("\n$prog:$file\nStale lock file found.\nAnother program $name is running on it..\n");
		}

		lxfile_rm($pidfile);
		lfile_put_contents($pidfile, os_getpid());
		return false;
	}
	return true;
}

function lx_core_lock_check_only($prog, $file = null)
{
	lxfile_mkdir("../pid");
	if (!$file) {
		$file = basename($prog). ".pid";
	} else {
		$file = basename($file);
	}

	$pidfile = "__path_program_root/pid/$file";

	if (!lxfile_exists($pidfile)) {
		return false;
	}

	$pid = lfile_get_contents($pidfile);
	dprint($pid . "\n");
	if (!$pid) {
		dprint("\n$prog:$file\nNo pid in file detected..\n");
		return false;
	}

	$pid = trim($pid);
	$name = os_get_commandname($pid);

	if ($name) {
		$name = basename($name);
	}

	if (!$name || $name !== $prog) {
                if (!$name) {
                        dprint("\n$prog:$file\nStale Lock file detected.\n$pidfile\nRemoving it...\n ");
                } else {
                        dprint("\n$prog:$file\nStale lock file found.\nAnother program $name is running on it..\n");
                }

		lxfile_rm($pidfile);
		return false;
	}
	return true;
}

function appvault_dbfilter($inputfile, $outputfile, $cont)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$val = lfile_get_contents($inputfile);
	$fullurl = "{$cont['domain']}/{$cont['installdir']}";
	$fullurl = trim($fullurl, "/");
	$full_install_path = "{$cont['full_document_root']}/{$cont['installdir']}";
	$full_install_path = remove_extra_slash($full_install_path);
	$full_install_path = trim($full_install_path, "/");
	$full_install_path = "/$full_install_path";
	$install_dir = $cont['installdir'];
	$install_dir = trim($install_dir, "/");
	$full_doc_root = $cont['full_document_root'];
	$full_doc_root = trim($full_doc_root, "/");
	$full_doc_root = "/$full_doc_root";

	if (isset($cont['relative_script_path'])) {
		$relative_script_path = $cont['relative_script_path'];
		$relative_script_path = remove_extra_slash("/$relative_script_path");
	} else {
		if (isset($cont['executable_file_path'])) {
			$execpath = $cont['executable_file_path'];
			$relative_script_path = remove_extra_slash("/$install_dir/$execpath");
		} else {
			$relative_script_path = $install_dir;
		}
	}

	$val = str_replace("__lx_full_url", $fullurl, $val);
	$val = str_replace("__lx_full_installdir", $full_install_path, $val);
	$val = str_replace("__lx_full_script_path", $full_install_path, $val);
	$val = str_replace("__lx_document_root", $full_doc_root, $val);
	$val = str_replace("__lx_installdir", $install_dir, $val);
	$val = str_replace("__lx_relative_script_path", $relative_script_path, $val);

	$val = str_replace("__lx_title", $cont['title'], $val);
	$val = str_replace("__lx_admin_email", $cont['email'], $val);
	$val = str_replace("__lx_admin_company", $cont['company'], $val);
	$val = str_replace("__lx_real_name", $cont['realname'], $val);
	$val = str_replace("__lx_install_flag", $cont['install_flag'], $val);
	$val = str_replace("__lx_admin_name", $cont['adminname'], $val);
	$val = str_replace("__lx_submit_value", $cont['submit_value'], $val);
	$val = str_replace("__lx_client_path", "/home/{$cont['customer_name']}", $val);
	$val = str_replace("__lx_adminemail_login", $cont['admin_email_login'], $val);
	$val = str_replace("__lx_admin_pass", $cont['adminpass'], $val);
	$val = str_replace("__lx_md5_adminpass", md5($cont['adminpass']), $val);
	$val = str_replace("__lx_db_host", $cont['realhost'], $val);
	$val = str_replace("__lx_db_name", $cont['dbname'], $val);
	$val = str_replace("__lx_db_pass", $cont['dbpass'], $val);
	$val = str_replace("__lx_db_user", $cont['dbuser'], $val);
	$val = str_replace("__lx_db_type", $cont['dbtype'], $val);
	$val = str_replace("__lx_url",$cont['domain'], $val);
	$val = str_replace("__lx_domain_name",$cont['domain'], $val);
	$val = str_replace("__lx_action",$cont['action'],$val);
	//dprint("Writing to file {$cont['output']}\n");
	//dprint("{$cont['output']} : $val\n");
	lfile_put_contents($outputfile, $val);
}

function installLxetc()
{
// TODO: Remove this function
	return;
}

function lightyApacheLimit($server, $var)
{
	if (!$server) { return true; }

	global $gbl, $sgbl, $login, $ghtml; 
	if ($var === 'frontpage_flag' || $var === 'phpfcgi_flag' || $var === 'phpfcgiprocess_num') {
		$driverapp = $gbl->getSyncClass(null, $server, 'web');
		if ($var === 'frontpage_flag' ) {
			$v = db_get_value("pserver",  $server, "osversion");
			if (csa($v, " 5")) { return false; }
			if ($driverapp === 'lighttpd') { return false; }
			return true;

		} else {
			if ($driverapp === 'apache') {
				return false;
			} else {
				return true;
			}
		}

	}
	if ($var === 'dotnet_flag') {
		$v = db_get_value("pserver", $server, "ostype");
		return ($v !== 'rhel');
	}

	return true;
}


function createRestartFile($servar)
{
	global $gbl, $sgbl, $login, $ghtml; 


	$servarn = "__var_progservice_$servar";

	if (isset($sgbl->$servarn)) {
		$service = $sgbl->$servarn;
	} else {
		$service = $servar;
	}

	$file = "__path_program_etc/.restart";
	lxfile_mkdir($file);
	$file .= "/._restart_" . $service;
	lfile_put_contents($file, "a");
}

function getLastFromList(&$list)
{
	if (!$list) {
		return null;
	}
	foreach($list as &$l) {
	}
	return $l;
}

function getFirstKeyFromList(&$list)
{
	if (!$list) {
		return null;
	}
	foreach($list as $k => &$l) {
		return $k;
	}
}

function getFirstFromList(&$list)
{
	if (!$list) {
		return null;
	}
	foreach($list as &$l) {
		return $l;
	}
}

function getBestLocationFromServer($server, $list)
{
	return rl_exec_get(null, $server, 'get_best_location', array($list));
}

function get_best_location($list)
{
	dprintr($list);
	$lvmlist = null;

	foreach($list as $l) {
		if (csb($l, "lvm:")) {
			$lvmlist[] = $l;
		} else {
			$normallist[] = $l;
		}
	}

	if ($lvmlist) {
		foreach($lvmlist as $l) {
			$out[$l] = vg_diskfree($l);
		}
	} else {
		foreach($normallist as $l) {
			$out[$l] = lxfile_disk_free_space($l);
		}
	}

	dprintr($out);
	arsort($out);
	dprintr($out);
	foreach($out as $k => $v) {
		return array('location' => $k, 'size' => $v);
	}
}

function vg_complete()
{
	if (!lxfile_exists("/usr/sbin/vgdisplay")) { return; }
	$out = exec_with_all_closed_output("vgdisplay -c");
	$list = explode("\n", $out);
	$ret = null;
	foreach($list as $l) {
		$l = trim($l);
		if (!$l) {
			continue;
		}
		if (!csa($l, ":")) {
			continue;
		}
		$nlist = explode(":", $l);
		$res['nname'] = $nlist[0];
		$res['total'] = ($nlist[13] * $nlist[12])/1024;
		$res['used'] = ($nlist[14] * $nlist[12])/1024;
		$ret[] = $res;
	}
	return $ret;
}

function vg_diskfree($vgname)
{
	if (!lxfile_exists("/usr/sbin/vgdisplay")) { return; }
	$vgname = fix_vgname($vgname);
	$out = exec_with_all_closed_output("vgdisplay -c $vgname");
	$out = trim($out);

	$list = explode(":", $out);

	$per = $list[12];
	$num = $list[15];

	return ($per * $num)/1024;
}

function lvm_disksize($lvmpath)
{
	//$out = exec_with_all_closed_output("lvdisplay -c /dev/$vgname/$lvmname");
	//$out = explode(":", $out);
	//return $out[6] / 1024;

	$out = exec_with_all_closed_output("/usr/sbin/lvs --nosuffix --units b --noheadings -o lv_size $lvmpath");
	$out = trim($out);
	return $out/ (1024 * 1024);


}

function lo_remove($loop)
{
	lxshell_return("losetup", "-d", $loop);
}

function lvm_remove($lvmpath)
{
	lxshell_return("lvremove", "-f", $lvmpath);
}

function lvm_create($vgname, $lvmname, $size)
{
	$vgname = fix_vgname($vgname);
	$lvmname = basename($lvmname);
	return lxshell_return("lvcreate", "-L{$size}M", "-n$lvmname", $vgname);
}

function lvm_extend($lvpath, $size)
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_shell_error;

	$cursize = lvm_disksize($lvpath);
	$extra = $size - $cursize;
	if ($extra > 0) {
		$ret = lxshell_return("lvextend", "-L+{$extra}M", $lvpath);
		if ($ret) {
			$gbl->setWarning('extending_failed', '', $global_shell_error);
		}
	}
}


function curl_get_file($file)
{
	$res = curl_get_file_contents($file);
	$res = trim($res);
	if (!$res) {
		return null;
	}
	$data = explode("\n", $res);
	return $data;
}

function curl_get_file_contents($file)
{
	$server = getDownloadServer();
	$ch = curl_init("$server/$file");
	ob_start();
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($code !== 200) {
		return null;
	}

	dprint(curl_error($ch));
	curl_close($ch);
	$retrievedhtml = ob_get_contents();
	ob_end_clean();
	return $retrievedhtml;
}

function install_if_package_not_exist($name)
{
	$ret = lxshell_return("rpm", "-q", $name);
	if ($ret) {
		lxshell_return("yum", "-y", "install", $name);
	}
}

function curl_general_get($url)
{
	$ch = curl_init($url);
	ob_start();
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($code !== 200) {
		return null;
	}

	dprint(curl_error($ch));
	curl_close($ch);
	$retrievedhtml = ob_get_contents();
	ob_end_clean();
	return $retrievedhtml;
}


function getFullVersionList($till = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;
	static $nlist;

	if ($nlist) {
		return $nlist;
	}

	$res = curl_get_file("$progname/version.txt");
	//dprintr($res);


	if (!$res) {
		throw new lxException('could_not_get_version_list', '');
	}

	foreach($res as $k => $l) {
		// Skip lines that do not start with progname or one that contains 'current'
		if (!csb($l, "$progname")) {
			continue;
		}
		if (csa($l, "current")) {
			continue;
		}

		$upversion = strfrom($l, "$progname-");
		$upversion = strtil($upversion, ".zip");
		$list[] = $upversion;
		if ($till) {
			if ($upversion === $till) {
				break;
			}
		}
	}

	return $list;
}

function getVersionList($till = null)
{
	$list = getFullVersionList($till);
	foreach($list as $k => $l) {
		if (preg_match("/2$/", $l) && ($k !== count($list) -1 )) {
			continue;
		}
		$nnlist[] = $l;
	}
	$nlist = $nnlist;
	return $nlist;
}

function checkIfLatest()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$latest = getLatestVersion();
	return ($latest === $sgbl->__ver_major_minor_release);
}

function getLatestVersion()
{
	$nlist = getVersionList();
	return $nlist[count($nlist)- 1];

}

function getDownloadServer()
{
	global $gbl, $sgbl, $login, $ghtml; 
	static $local;

	$progname = $sgbl->__var_program_name;
	$maj = $sgbl->__ver_major_minor;
	$server = "http://download.lxcenter.org/download/$progname/$maj";

	return $server;
}

function download_source($file)
{
	
	$server = getDownloadServer();
	download_file("$server/$file");
}

function download_from_ftp($ftp_server, $ftp_user, $ftp_pass, $file, $localfile)
{
	$fn = ftp_connect($ftp_server);
	$login = ftp_login($fn, $ftp_user, $ftp_pass);
	if (!$login) {
		throw new lxException('could_not_connect_to_ftp_server', 'download_ftp_f', $ftp_server);
	}
	ftp_pasv($fn, true);
	$fp = lfopen($localfile, "w");
	if (!ftp_fget($fn, $fp, $file, FTP_BINARY)) {
		throw new lxException('file_download_failed', '', $file);
	}
	fclose($fp);
}


function incrementVar($table, $var, $min, $increment)
{
	$sq = new Sqlite(null, $table);
	$res = $sq->rawQuery("select $var from $table order by ($var + 0) DESC limit 1");


	if (!$res) {
		$ret = $min;
	} else {
		$ret = $res[0][$var] + $increment;
	}

	return $ret;
}


function download_file($url, $localfile = null)
{
	log_log("download", "$url $localfile");
	$ch = curl_init($url);
	if (!$localfile) {
		$localfile = basename($url);
	}
	$fp = null;
	if ($localfile !== 'devnull') {
		$fp = lfopen($localfile, "w");
		curl_setopt($ch, CURLOPT_FILE, $fp);
	}
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_exec($ch);
	dprint("Curl Message: " . curl_error($ch) . "\n");
	curl_close($ch);
	if ($fp) {
		fclose($fp);
	}
}

function se_submit($contact, $dom, $email)
{
	$tmpfile = lx_tmp_file("se_submit_$dom");
	include "sesubmit/engines.php";
	foreach($enginelist as $e => $k) {
		$k = str_replace("[>URL<]", "http://$dom", $k);
		$k = str_replace("[>EMAIL<]", $email, $k);
		download_file($k, $tmpfile);
		$var .= "\n\n-----------Submitting to $e-------------\n\n";
		$var .= lfile_get_contents($tmpfile);
	}
	lunlink($tmpfile);
	lx_mail("kloxo", $contact, "Search Submission Info", $var);
	lfile_put_contents("/tmp/mine", $var);
}

function remove_if_older_than_a_day_dir($dir, $day = 1)
{
	if (!lis_dir($dir)) { return; }
	$list = lscandir_without_dot($dir);
	foreach($list as $l) {
		remove_if_older_than_a_day("$dir/$l", $day);
	}
}

function remove_if_older_than_a_day($file, $day = 1)
{
	$stat = llstat($file);

	if ($stat['mtime'] && ((time() - $stat['mtime']) > $day * 24 * 3600)) {
		lunlink($file);
	}
}

function remove_directory_if_older_than_a_day($dir, $day = 1)
{
	$stat = llstat($dir);

	if ($stat['mtime'] && ((time() - $stat['mtime']) > $day * 24 * 3600)) {
		lxfile_rm_rec($dir);
	}
}

function remove_if_older_than_a_minute_dir($dir)
{
	$list = lscandir_without_dot($dir);
	foreach($list as $l) {
		remove_if_older_than_a_minute("$dir/$l");
	}
}

function remove_if_older_than_a_minute($file)
{
	$stat = llstat($file);

	if ($stat['mtime'] && ((time() - $stat['mtime']) > 60)) {
		lunlink($file);
	}
}

function lx_mail($from, $to, $subject, $message, $extra = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$from) {
		$progname = $sgbl->__var_program_name;
		$server = getFQDNforServer('localhost');
		$from = "$progname@$server";
	}

	$header = "From: $from";
	if ($extra) {
		$header .= "\n$extra";
	}


	log_log("mail_send", "Sending Mail to $to $subject from $from");

	mail($to, $subject, $message, $header);
}

function download_and_print_file($server, $file)
{
	$ch = curl_init("$server/$file");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_exec($ch);
	curl_close($ch);
}

function get_title()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gen = $login->getObject('general')->generalmisc_b;

	if ($login->isAdmin()) {
		$host = os_get_hostname();
		$host = strtilfirst($host, ".");
	} else {
		$host = $login->nname;
	}

	if (isset($gen->htmltitle) && $gen->htmltitle) {
		$progname = $gen->htmltitle;
	} else {
		$progname = ucfirst($sgbl->__var_program_name);
	}

	$title = null;
	if ($login->isAdmin()) {
		$title = $sgbl->__ver_major . "." . $sgbl->__ver_minor . "." . $sgbl->__ver_release . " " . $sgbl->__ver_extra;
	}
	if (check_if_many_server()) {
		$enterprise = "Enterprise";
	} else {
		$enterprise = "Single Server";
	}
	if (file_exists(".svn")) {
		$enterprise .= " Development";
	}
	$title = "$host $progname $enterprise $title" ;
	return $title;
}

function send_mail_to_admin($subject, $message)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;

	$rawdb = new Sqlite(null, "client");
	$email = $rawdb->rawQuery("select contactemail from client where cttype = 'admin'");
	$email = $email[0]['contactemail'];

	callInBackground("lx_mail", array($progname, $email, $subject, $message));
}

function save_admin_email()
{
	$a = null;
	$email = db_get_value("client", "admin", "contactemail");
	$a['admin']['contactemail'] = $email;
	slave_save_db("contactemail", $a);
}

function callInChild($func, $arglist)
{
	$res = new Remote();
	$res->__type = 'function';
	$res->func = $func;
	$res->arglist = $arglist;
	$name = tempnam("/tmp", "lxchild");
	lxfile_generic_chmod($name, "700");
	lfile_put_contents($name, serialize($res));
	$var = lxshell_output("__path_php_path", "../bin/common/child.php", $name);
	$rmt = unserialize(base64_decode($var));
	return $rmt;
}

function callInBackground($func, $arglist)
{
	$res = new Remote();
	$res->__type = 'function';
	$res->func = $func;
	$res->arglist = $arglist;
	$name = tempnam("/tmp", "background");
	lxfile_generic_chmod($name, "700");
	lfile_put_contents($name, serialize($res));
	lxshell_background("__path_php_path", "../bin/common/background.php", $name);
}

function callObjectInBackground($object, $func)
{
	$res = new Remote();
	$res->__type = 'object';
	$res->__exec_object = $object;
	$res->func = $func;
	$name = tempnam("/tmp", "background");
	lxfile_generic_chmod($name, "700");
	lfile_put_contents($name, serialize($res));
	lxshell_background("__path_php_path", "../bin/common/background.php", $name);
}


function get_with_cache($file, $cmdarglist)
{
	global $global_shell_out, $global_shell_error, $global_shell_ret;
	$stat = @ llstat($file);

	lxfile_mkdir("__path_program_root/cache");
	$tim = 120;
	//$tim = 1;
	$c = lfile_get_contents($file);
	if (((time() - $stat['mtime']) > $tim) || !$c) {
		// Hack hack.. The lxshell_output does not take strings. You need to supply them together.
		$val = call_user_func_array('lxshell_output', $cmdarglist);
		lfile_put_contents($file, $val);
		return $val;
	}

	return lfile_get_contents($file);

}

function copy_script()
{
	global $gbl, $sgbl, $login, $ghtml; 
	lxfile_tmp_rm_rec("/script");
	lxfile_mkdir("/script");
	lxfile_mkdir("/script/filter");

	lxfile_cp_content_file("htmllib/script/", "/script/");
	lxfile_cp_content_file("../pscript", "/script/");

	if (lxfile_exists("../pscript/vps/")) {
		lxfile_mkdir("/script/vps");
		lxfile_cp_content_file("../pscript/vps/", "/script/vps/");
	}


	lxfile_cp_content_file("../pscript/filter/", "/script/filter/");
	lxfile_cp_content_file("htmllib/script/filter/", "/script/filter/");


	lfile_put_contents("/script/programname", $sgbl->__var_program_name);
	lxfile_unix_chmod_rec("/script", "0755");
}

function copy_image()
{
	// Not needed anymore - LxCenter
	return; 
	global $gbl, $sgbl, $login, $ghtml; 
	$prgm = $sgbl->__var_program_name;

	lxfile_cp_content("tmpimg/", "img/image/collage/button/");
	$list = lscandir_without_dot("img/skin/$prgm/feather/");
	foreach($list as $l) {
		lxfile_cp_content("tmpskin/", "img/skin/$prgm/feather/$l");
	}
}

function getAdminDbPass()
{
	$pass = lfile_get_contents("__path_admin_pass");
	return trim($pass);
}

function change_underscore($var)
{
	$var = str_replace("_", " ", $var);
	
	if (csa($var, ":")) {
		$n = strpos($var, ":");
		$var[$n + 1] = strtoupper($var[$n + 1]);
	}
	return ucwords($var);
}

function getIpaddressList($master, $servername)
{
	$sql = new Sqlite($master, 'ipaddress');
	if (!$servername) {
		$servername = 'localhost';
	}
	$list = $sql->getRowsWhere("syncserver = '$servername'");
	foreach($list as $l) {
		$ret[] = $l['ipaddr'];
	}
	return $ret;
}

function if_customer_complain_and_exit()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($login->isLte('reseller')) {
		return;
	}

	$progname = $sgbl->__var_program_name;
		
	print("You are trying to access Protected Area. This incident will be reported\n <br> ");

	$message = "At " . lxgettime(time()) . " $login->nname tried to Access a region that is prohibited for Normal Users\n";

	send_mail_to_admin("$progname Warning: Unauthorized Access by $login->nname", $message);

	exit(0);

}

function getClassAndName($name)
{
	return getParentNameAndClass($name);
}

function getParentNameAndClass($pclname)
{
	return dogetParentNameAndClass($pclname);
}

function dogetParentNameAndClass($pclname)
{
	if (csa($pclname, "-")) { $string = "-"; } else { $string = "_s_vv_p_"; }

	//$vlist = explode("_s_vv_p_", $pclname);
	$vlist = explode($string, $pclname);
	$pclass = array_shift($vlist);
	//$pname = implode("_s_vv_p_", $vlist);
	$pname = implode($string, $vlist);

	//dprint($pclass);

	return array($pclass, $pname);

}

function doOldgetParentNameAndClass($pclname)
{
	if (csa($pclname, "_s_vv_p_")) { $string = "_s_vv_p_"; } else { $string = "-"; }

	//$vlist = explode("_s_vv_p_", $pclname);
	$vlist = explode($string, $pclname);
	$pclass = array_shift($vlist);
	//$pname = implode("_s_vv_p_", $vlist);
	$pname = implode($string, $vlist);

	//dprint($pclass);

	return array($pclass, $pname);

}

function if_not_admin_complain_and_exit()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$progname = $sgbl->__var_program_name;
	if ($login->isLteAdmin()) {
		return;
	}
		
	print("You are trying to access Protected Area. This incident will be reported\n <br> ");
	debugBacktrace();

	$message = "At " . lxgettime(time()) . " $login->nname tried to Access a region that is prohibited for Normal Users\n";

	send_mail_to_admin("$progname Warning: Unauthorized Access by $login->nname", $message);

	exit(0);

}

function initProgram($ctype = NULL)
{
	global $gbl, $sgbl, $login, $ghtml;
  
	initProgramlib($ctype);

}

function getKBOrMB($val)
{
	if ($val > 1014) {
		return round($val/1024, 2) . " MB";
	} 
	return "$val KB";
}

function getGBOrMB($val)
{
	if ($val > 1014) {
		return round($val/1024, 2) . " GB";
	} 
	return "$val MB";
}

function createClName($class, $name)
{
	return "{$class}-$name";
	//return "{$class}_s_vv_p_$name";

}
function createParentName($class, $name)
{
	return $class . "-" . $name;
	//return $class . "_s_vv_p_" . $name;

}

function exists_in_coma($cmlist, $name)
{
	return (csa($cmlist, ",$name,"));
}

function exit_program()
{
	global $gbl, $sgbl, $login, $ghtml; 

	print_time('full', "Page Generation Took: ");

	exit_programlib();
}

function install_general($value)
{
	$value = implode(" ", $value);
	print("Installing $value ....\n");
	system("up2date-nox --nosig $value");
}

function readlastline($fp, $pos, $size)
{

	$t = " ";
	while ($t != "\n") {
		fseek($fp , $pos, SEEK_END);
		$t = fgetc($fp);
		$pos = $pos - 1;
		if($pos === -$size) {
			$pos = null;
			break;
		}
	
	}
	$t = fgets($fp);
	return $t ;
}

function getMainQuotaVar($vlist)
{
	$vlist['disk_usage'] = "";      
	$vlist['traffic_usage'] = "";   
	$vlist['mailaccount_num'] = ""; 
	$vlist['subweb_a_num'] = ""; 
	$vlist['ftpuser_num'] = ""; 
	$vlist['ddatabase_num'] = "";    
	$vlist['subweb_a_num'] = "";    
	$vlist['ssl_flag'] = "";    
	$vlist['inc_flag'] = "";    
	$vlist['php_flag'] = "";    
	$vlist['modperl_flag'] = "";    
	$vlist['cgi_flag'] = "";    
	$vlist['frontpage_flag'] = "";    
	$vlist['dns_manage_flag'] = "";    
	$vlist['maildisk_usage'] = "";
	return $vlist;
}

function get_domain_client_temp_list($class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$temp=Array();
	$list= $login->getList($class);
	foreach($list as $d) {
		$temp[$d->nname] = $d;
	}
	return $temp;
}

function manage_service($service, $state)
{
	global $gbl, $sgbl, $login, $ghtml; 
	print("Sending $state to $service\n");
	$servicename = "__var_programname_$service";
	$program = $gbl->$servicename;
	lxshell_return("/etc/init.d/$program", $state);
}

function recursively_remove($directory)
{
	$directory = trim($directory);
	if ($directory[strlen($directory) - 1] === '/') {
		$string = "$directory: Directory ends in a slash. Will not recursively delete";
		dprint(' <br> ' . $string . "<br> ");
		log_shell_error($string);
		return;
	}
	lxfile_rm_rec($directory);

}
function checkIfRightTime($time, $first, $second)
{

	if ($time === $first || $time === $second || ($time > $first && $time < $second)) {
		return 0;
	}

	if ($time > $second) {
		return 1;
	}

	if ($time < $first) {
		return -1;
	}
}

function is_ip($ipf, $ip)
{
	$if = explode(".", $ipf);
	$ii = explode(".", $ip);
	foreach($if as $k => $v) {
		if ($v === '*') {
			continue;
		}
		if ($v !== $ii[$k]) {
			return false;
		}
	}
	return true;
}

function get_star_password()
{
	return "****";
}

function is_star_password($pass)
{
	return ($pass === "****");
}

function FindRightPosition($fp, $fsize, $oldtime, $newtime, $func) 
{
	$cur = $fsize/2;
	$beg = 0;
	$end = $fsize;

	dprint($cur . "\n");

	$string = fgets($fp);
	$begtime = call_user_func($func, $string);

	if ($newtime < $begtime) {
		dprint("ENd time $newtime < $begtime Less than Beginning. \n");
		print("Date: " .@ date('Y-m-d: H:i:s', $newtime) . " " . @ date('Y-m-d: h:i:s', $begtime). "\n");
		return -1;
	}

	/* This logic is actually wrong. This is returning if the oldtime is less than first time, but that isn't is a necessary criteria. The file could be so small as to start from middle of the day.
	if ($time < $readtime) {
		dprint("Less than Beginning. \n");
		return 0;
	}
*/

	fseek($fp, 0, SEEK_END);
	takeToStartOfLine($fp);
	$string = fgets($fp);

	$endtime = call_user_func($func, $string);
	if ($oldtime > $endtime) {
		$ot = @ date("Y-m-d:h-i", $oldtime);
		dprint(" $ot $oldtime $string More than End. \n");
		return -1;
	}
	rewind($fp);

	if ($oldtime < $begtime) {
		return 1;
	}

	$count = 0;
	while(true) {
		$count++;
		if ($count > 1000) {
			return -1;
		}
		dprint("At position $cur: \n");
		fseek($fp, $cur);

		takeToStartOfLine($fp);

		$string1 = fgets($fp);
		$readtime1 = call_user_func($func, $string1);
		$string2 = fgets($fp);
		$readtime2 = call_user_func($func, $string2);

		dprint("Position: $oldtime $readtime1 $readtime2\n");
		if ($readtime2 - $readtime1 >= 100) {
			dprint("Somethings wrong $string1 $string2 \n");
		}


		$ret = checkIfRightTime($oldtime, $readtime1, $readtime2);

		if ($ret === 0) {
			takeToStartOfLine($fp);
			return 1;
		} else if ($ret < 0) {
			dprint("Going Up\n");
			$end = $cur;
			$cur = $cur - ($cur - $beg)/2;
			$cur = round($cur);
		} else {
			dprint("Going Down\n");
			$beg = $cur;
			$cur = $cur + ($end - $cur)/2;
			$cur = round($cur);
		}
	}
}

function lxlabs_marker_fgets($fp)
{
	global $gbl, $sgbl, $login, $ghtml; 
	while(!feof($fp)) {
		$s = fgets($fp);
		if (csa($s, $sgbl->__var_lxlabs_marker)) {
			dprint("found marker\n");
			return $s;
		}
	}
	return null;
}

function lxlabs_marker_getime($string)
{
	$str = strtilfirst($string, " ");
	$str = trim($str);
	return $str;
}

function lxlabs_marker_firstofline($fp)
{
	global $gbl, $sgbl, $login, $ghtml; 
	while(!feof($fp)) {
		if (ftell($fp) <= 2) { return; }
		takeToStartOfLine($fp);
		takeToStartOfLine($fp);
		$string = fgets($fp);
		if (csa($string, $sgbl->__var_lxlabs_marker)) {
			takeToStartOfLine($fp);
			return;
		}
	}
}


function lxlabsFindRightPosition($fp, $fsize, $oldtime, $newtime)
{
	$cur = $fsize/2;
	$beg = 0;
	$end = $fsize;

	dprint($cur . "\n");

	$string = lxlabs_marker_fgets($fp);

	if (!$string) {
		dprint("Got nothing\n");
		return -1; 
	}

	$begtime = lxlabs_marker_getime($string);

	if ($newtime < $begtime) {
		dprint("ENd time $newtime < $begtime Less than Beginning. \n");
		print("Date: " .@ date('Y-m-d: H:i:s', $newtime) . " " . @ date('Y-m-d: h:i:s', $begtime). "\n");
		return -1;
	}

	/* This logic is actually wrong. This is returning if the oldtime is less than first time, but that isn't is a necessary criteria. The file could be so small as to start from middle of the day.
	if ($time < $readtime) {
		dprint("Less than Beginning. \n");
		return 0;
	}
*/

	fseek($fp, 0, SEEK_END);
	lxlabs_marker_firstofline($fp);

	$string = lxlabs_marker_fgets($fp);

	$endtime = lxlabs_marker_getime($string);
	if ($oldtime > $endtime) {
		$ot = @ date("Y-m-d:h-i", $oldtime);
		dprint(" $ot $oldtime $string More than End. \n");
		return -1;
	}

	rewind($fp);

	if ($oldtime < $begtime) {
		return 1;
	}

	$count = 0;
	while(true) {

		$count++;

		if ($count > 1000) { return -1; }

		dprint("At position $cur: \n");
		fseek($fp, $cur);

		lxlabs_marker_firstofline($fp);

		$string1 = lxlabs_marker_fgets($fp);
		$readtime1 = lxlabs_marker_getime($string1);
		$string2 = lxlabs_marker_fgets($fp);
		$readtime2 = lxlabs_marker_getime($string2);

		dprint("Position: $oldtime $readtime1 $readtime2\n");
		if ($readtime2 - $readtime1 >= 10*300) {
			dprint("Somethings wrong $string1 $string2 \n");
		}


		$ret = checkIfRightTime($oldtime, $readtime1, $readtime2);

		if ($ret === 0) {
			lxlabs_marker_firstofline($fp);
			return 1;
		} else if ($ret < 0) {
			dprint("Going Up\n");
			$end = $cur;
			$cur = $cur - ($cur - $beg)/2;
			$cur = round($cur);
		} else {
			dprint("Going Down\n");
			$beg = $cur;
			$cur = $cur + ($end - $cur)/2;
			$cur = round($cur);
		}
	}
}

function monthToInt($month) 
{
	$t ="";

	switch($month) {
	
 	case "Jan": $t = 1;
	             break;
	case "Feb": $t = 2;
	             break;
	case "Mar": $t = 3;
	             break;
	case "Apr": $t = 4;
	             break;
	case "May": $t = 5;
	             break;
	case "Jun": $t = 6;
	             break;
	case "Jul": $t = 7;
	             break;
	case "Aug": $t = 8;
	             break;
	case "Sep": $t = 9;
	             break;
	case "Oct": $t = 10;
	             break;
	case "Nov": $t = 11;
	             break;
	case "Dec": $t = 12;
	             break;
	}

	return str_pad($t , 2, 0 , STR_PAD_LEFT);
}

function intToMonth($month) 
{

	$mon = 0;
	switch($month) {

		case "01":
			$mon = "Jan";
			break;

		case "02":
			$mon = "Feb";
			break;

		case "03":
			$mon = "Mar";
			break;

		 case "04":
			 $mon = "Apr";
			 break;

		case "05":
			$mon = "May";
			break;

		case "06":
			$mon = "Jun";
			break;
  
		case "07":
			$mon = "Jul";
			break;

		case "08":
			$mon = "Aug";
			break;

		case "09":
			$mon = "Sep";
			break;

		case "10":
			$mon = "Oct";
			break;

		case "11":
			$mon = "Nov";
			break;

		case "12":
			$mon = "Dec";
			break;
	 }

	return $mon;
} 

function readfirstline($file){
	$firstline   = fgets($file);
	fclose($fp);
	return $firstline;
}

function getNotexistingFile($dir, $file)
{
	foreach(range(1, 100) as $i) {
		if (!lxfile_exists($dir . "/" . $file . "-" . $i))  {
			return $dir . "/" . $file . "-" . $i;
		}
	}
	return $dir . "/" . $file . "-" . $i;

}

function clearLxbackup($backup)
{
	$backup->setUpdateSubaction();
	$backup->write();
}

function createrows($list)
{
	$fields = lx_array_merge(array(get_default_fields(), $list));
	if (array_search_bool("syncserver", $fields)) {
		$fields[] = 'oldsyncserver';
		$fields[] = 'olddeleteflag';
	}
	return $fields;
}

function initDbLoginPre()
{
	$log_pre = "<p> Welcome to <%programname%>  </p><p>Use a valid username and password to gain access to the console. </p> ";
	db_set_default('general', 'login_pre', $log_pre);
}

function fixResourcePlan()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$login->loadAllObjects('resourceplan');
	$list = $login->getList('resourceplan');
	foreach($list as $l) {
		$qv = getQuotaListForClass('client');
		$write = false;
		foreach($qv as $k => $v) {

			if ($k === 'centralbackup_flag') {
				if (!isset($l->priv->centralbackup_flag)) {
					$l->priv->centralbackup_flag = $l->centralbackup_flag;
					$write = true;
				}
				continue;
			}

			if (!isset($l->priv->$k)) {
				if (cse($k, "_flag")) {
					if (is_default_quota_flag_on($k)) {
						$l->priv->$k = 'on';
						$write = true;
					}
				}
			}
		}

		if ($write) {
			$l->setUpdateSubaction();
			$l->write();
			$write = false;
		}
	}
}

function is_default_quota_flag_on($v)
{
	if ($v === 'mailonly_flag') {
		return false;
	}

	return true;
}

function db_set_default($table, $variable, $default, $extra = null)
{
	$sq = new Sqlite(null, $table);
	if ($extra) {
		$extra = "AND $extra";
	}
	$sq->rawQuery("update $table set $variable = '$default' where $variable = '' $extra");
	$sq->rawQuery("update $table set $variable = '$default' where $variable is null $extra");
}

function db_set_default_variable_diskusage($table, $variable, $default, $extra = null)
{
	$sq = new Sqlite(null, $table);
	if ($extra) {
		$extra = "AND $extra";
	}
	$sq->rawQuery("update $table set $variable = $default where $variable = '' $extra");
	$sq->rawQuery("update $table set $variable = $default where $variable is null $extra");
	$sq->rawQuery("update $table set $variable = $default where $variable = '-' $extra");
}

function db_set_default_variable($table, $variable, $default, $extra = null)
{
	$sq = new Sqlite(null, $table);
	if ($extra) {
		$extra = "AND $extra";
	}
	$sq->rawQuery("update $table set $variable = $default where $variable = '' $extra");
	$sq->rawQuery("update $table set $variable = $default where $variable is null $extra");
	//$sq->rawQuery("update $table set $variable = $default where $variable = '-' $extra");
}

function updateTableProperly($__db, $table, $rr, $content)
{
	foreach($content as $column) {
		if (isset($rr[$column])) {
			//dprint("Column $column Already exists in table $table\n");
			continue;
		}

		if (csb($column, "text_") || csb($column, "ser_") || csb($column, "coma_")) {
			$type = "text";
		} else {
			$type = "varchar(255)";
		}

		dprint("Adding column $column to $table ...\n");

		$__db->rawQuery("alter table $table add column $column $type");
	}
	return true;
}

function add_http_if_not_exist($url)
{
	if (!csb($url, "http:/") && !csb($url, "https:/")) {
		$url = "http://$url";
	}
	return $url;
}

function getAllIpaddress()
{
	$mydb = new Sqlite(null, 'ipaddress');
	$res = $mydb->getTable(array('ipaddr', 'nname'));

	foreach($res as $r) {
		$list[] = $r['ipaddr'];
	}
	return $list;
}

function updateDatabaseProperly()
{
	$var = parse_sql_data();

	foreach($var as $table => $content) {
		$__db = new Sqlite(null, $table);
		$res = $__db->getColumnTypes();
		if ($res) {
			//dprint("Table $table Already exists\n");
			updateTableProperly($__db, $table, $res, $content);
		} else {
			dprint("Adding table $table \n");
			create_table($__db, $table, $var[$table]);
		}
	}


}

function dofixParentClname()
{
	$var = parse_sql_data();

	foreach($var as $table => $content) {
		$__db = new Sqlite(null, $table);
		if ($table === 'ticket') {
			$list = array("parent_clname", "made_by", "sent_to");
		} else if ($table === 'smessage') {
			$list = array("parent_clname", "made_by");
		} else if ($table === 'kloxolicense') {
			$list = array("parent_clname", "created_by");
		} else if ($table === 'hypervmlicense') {
			$list = array("parent_clname", "created_by");
		} else {
			$list = array("parent_clname");
		}
		$get = lx_array_merge(array(array('nname'), $list));
		$res = $__db->getTable($get);

		if (!$res) { continue;} 
		foreach($res as $r) {

			foreach($list as $l) {
				$v = fix_getParentNameAndClass($r[$l]);
				if (!$v) { continue; }
				list($parentclass, $parentname) = $v;
				$npcl = "$parentclass-$parentname";
				$__db->rawQuery("update $table set $l = '$npcl' where nname = '{$r['nname']}'");
			}

			$spl = array('notification', 'serverweb', 'lxbackup', 'phpini');
			if (csb($table, "sp_") || array_search_bool($table, $spl)) {
				$v = fix_getParentNameAndClass($r['nname']);
				if (!$v) { continue; }
				list($parentclass, $parentname) = $v;
				$npcl = "$parentclass-$parentname";
				$__db->rawQuery("update $table set nname = '$npcl' where nname = '{$r['nname']}'");
			}
		}
	}
}

function fix_getParentNameAndClass($v)
{
	if (csa($v, "___") && !csa($v, "__last_access_")) {
		$vv = explode("___", $v);
		if (!csa($vv[0], "_s_vv_p_")) {
			return false;
		} else {
			return doOldgetParentNameAndClass($v);
		}
	} else {
		if (!csa($v, "_s_vv_p_")) {
			return false;
		} else {
			return doOldgetParentNameAndClass($v);
		}
	}

}

function get_table_from_class($class)
{
	$table =  get_class_variable($class, "__table");
	if (!$table) {
		return $class;
	}
	return $table;
}

function get_class_for_table($table)
{
	if ($table === 'domain') {
		return array('domaina', 'subdomain');
	}
	return null;
}

function is_centosfive()
{
	$cont = lfile_get_contents("/etc/redhat-release");
	if (csa($cont, " 5 ") || csa($cont, " 5.")) {
		return true;
	} 
	return false;
}


function migrateResourceplan($class)
{
	$ss = new Sqlite(null, "resourceplan");
	$r = $ss->getTable();
	if ($r) { return; }

	$sq = new Sqlite(null, 'clienttemplate');
	$cres = $sq->getTable();

	if ($class) {
		$nsq = new Sqlite(null, "{$class}template");
		$dres = $nsq->getTable();
		$total = lx_array_merge(array($cres, $dres));
	} else {
		$total = $cres;
	}

	foreach($total as $t) {
		$string = $ss->createQueryStringAdd($t);
		$addstring = "insert into resourceplan $string;";
		$ss->rawQuery($addstring);
	}
}

function fprint($var, $type = 0)
{
	global $sgbl ;
	if ($type > $sgbl->dbg) {
		return;
	}
	$string = var_export($var, true);
	file_put_contents("file.txt", $string ."\n", FILE_APPEND);
}

function print_and_exit($rem)
{
	$val = base64_encode(serialize($rem));
	ob_end_clean();
	print($val);
	flush();
	exit;
}

function getOsForServer($servername)
{
	if (!$servername) {
		$servername = 'localhost';
	}

	$sq = new Sqlite(null, 'pserver');

	$res = $sq->getRowsWhere("nname = '$servername'", array('ostype'));
	return $res[0]['ostype'];
}

function rl_exec_in_driver($parent, $class, $function, $arglist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$syncserver = $parent->getSyncServerForChild($class);
	$driverapp = $gbl->getSyncClass($parent->__masterserver, $syncserver, $class);
	$res = rl_exec_get($parent->__masterserver, $syncserver,  array("{$class}__$driverapp", $function), $arglist);
	return $res;
}

function vpopmail_get_path($domain)
{
	return trim(lxshell_output("__path_mail_root/bin/vdominfo", "-d", $domain));
}

function addLineIfNotExistPattern($filename, $searchpattern, $pattern)
{
	$cont = lfile_get_contents($filename);

	if(!preg_match("+$searchpattern+i", $cont)) {
		lfile_put_contents($filename, "\n", FILE_APPEND);
		lfile_put_contents($filename, $pattern, FILE_APPEND);
		lfile_put_contents($filename, "\n\n\n", FILE_APPEND);
	} else {
		dprint("Pattern '$searchpattern' Already present in $filename\n");
	}

}

function fix_self_ssl()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$pgm = $sgbl->__var_program_name;
	$ret = lxshell_return("diff", "../etc/program.pem", "htmllib/filecore/old.program.pem");

	if (!$ret) {
		lxfile_cp("htmllib/filecore/program.pem", "../etc/program.pem");
	}
	//system("/etc/init.d/$pgm restart");

}

function remove_line($filename, $pattern)
{
	$list = lfile($filename);

	foreach($list as $k => $l) {
		if (csa($l, $pattern)) {
			unset($list[$k]);
		}
	}
	lfile_put_contents($filename, implode("", $list));
}

function add_line($filename, $pattern)
{
	lfile_put_contents($filename, "$pattern\n", FILE_APPEND);
}

function addLineIfNotExistInside($filename, $pattern, $comment)
{
	$cont = lfile_get_contents($filename);

	if(!csa(strtolower($cont), strtolower($pattern))) {
		if ($comment) {
			lfile_put_contents($filename, "\n$comment \n\n", FILE_APPEND);
		}
		lfile_put_contents($filename, "$pattern\n", FILE_APPEND);
		if ($comment) {
			lfile_put_contents($filename, "\n\n\n", FILE_APPEND);
		}
	} else {
		//dprint("Pattern '$pattern' Already present in $filename\n");
	}

}

function fix_all_mysql_root_password()
{
	$rs = get_all_pserver();
	foreach($rs as $r) {
		fix_mysql_root_password($r);
	}
}

function fix_mysql_root_password($server)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$pass = $login->password;
	$pass = fix_nname_to_be_variable($pass);
	$pass = substr($pass, 3, 11);

	$dbadmin = new Dbadmin(null, $server, "mysql___$server");
	$dbadmin->get();

	if ($dbadmin->dbaction === 'add') {
		$dbadmin->syncserver = $server;
		$dbadmin->ttype = 'mysql';
		$dbadmin->dbtype = 'mysql';
		$dbadmin->dbadmin_name = 'root';
		$dbadmin->parent_clname = createParentName("pserver", $server);
		$dbadmin->write();
		$dbadmin->get();
		$dbadmin->dbaction = 'clean';
	}

	if ($dbadmin->dbpassword) {
		dprint("Mysql Password is not null\n");
		return;
	}
	$dbadmin->dbpassword = $pass;
	$dbadmin->setUpdateSubaction('update');
	try {
		$dbadmin->was();
	} catch (exception $e) {
	}
}

function slave_save_db($file, $list)
{
	$rmt = new Remote();
	$rmt->data = $list;
	lxfile_mkdir("../etc/slavedb");
	lfile_put_serialize("../etc/slavedb/$file", $rmt);
}

function securityBlanketExec($table, $nname, $variable, $func, $arglist)
{
	$rem = new Remote();
	$rem->table = $table;
	$rem->nname = $nname;
	$rem->flagvariable = $variable;
	$rem->func = $func;
	$rem->arglist = $arglist;
	$name = tempnam("/tmp", "security");
	lxfile_generic_chmod($name, "700");
	lfile_put_contents($name, serialize($rem));
	lxshell_background("__path_php_path", "../bin/common/securityblanket.php", $name);
}

function checkClusterDiskQuota()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$maclist = $login->getList('pserver');

	$mess = null;
	foreach($maclist as $mc) {
		try {
			rl_exec_get(null, $mc->nname, "remove_old_serve_file", null);
		} catch (exception $e) {
		}

		$driverapp = $gbl->getSyncClass(null, $mc->nname, 'diskusage');
		try {
			$list = rl_exec_get(null, $mc->nname, array("diskusage__$driverapp", "getDiskUsage"));
		} catch (exception $e) {
			$mess .= "Failed to connect to Slave $mc->nname: {$e->getMessage()}\n";
			continue;
		}

		foreach($list as $l) {
			if (intval($l['pused']) >= 87) {
				$mess .= "Filesystem  {$l['mountedon']} ({$l['nname']}) on {$mc->nname} is using {$l['pused']}%\n";
			}
		}
	}


	dprint($mess);
	dprint("\n");
	if ($mess) {
		lx_mail(null, $login->contactemail, "Filesystem Warning" , $mess);
	}

	lxfile_generic_chown("..", "lxlabs");
}

function find_closest_mirror()
{
    // TODO LxCenter: No call to this function found.
	dprint("find_closest_mirror htmllib>lib>lib.php\n"); 
	$v = curl_general_get("lxlabs.com/mirrorlist/");
	$v = trim($v);
	$vv = explode("\n", $v);
	$out = null;
	foreach($vv as $k => $l) {
		$l = trim($l);
		if (!$l) { continue; }
		$verify = curl_general_get("$l/verify.txt");
		$verify = trim($verify);
		if (csa($verify, "lxlabs_mirror_verify")) {
			$out[] = $l;
		}
	}
	if (!$out) { return null; }

	foreach($out as $l) {
		$hop[$l] = find_hop($l);
	}

	asort($hop);
	$v = getFirstKeyFromList($hop);
	return $v;

}

function find_hop($l)
{
	global $global_dontlogshell;
	$global_dontlogshell = true;
	$out = lxshell_output("ping -c 1 $l");
	$list = explode("\n", $out);
	foreach($list as $l) {
		$l = trim($l);
		if (csb($l, "rtt")) { continue; }
		$l = trimSpaces($l);
		$ll = explode(" ", $l);
		$lll = explode("/", $ll[3]);
		return round($lll[1], 1);;
	}
}

function file_server($fd, $string)
{
	$string = strfrom($string, "__file::");
	$rem = unserialize(base64_decode($string));
	if (!$rem) {
		return;
	}
	return do_serve_file($fd, $rem);
}

function print_or_write($fd, $buff)
{
	if ($fd) {
		return fwrite($fd, $buff);
	} else {
		print($buff);
		flush();
		// Lighttpd bug. Lighty doesn't flush even if you do a flush.
		//sleep(2);
		return 1;
	}
}

function get_warning_for_server_info($o, $psi)
{
	if ($o->isAdmin()) {
		$psi = "\n Only the servers that are visible in the main server list will be shown here. So if you have done some search in the main servers page, only search results will be seen. Just go to the main servers page, and limit the servers to the ones you want to see. \n$psi";
	}
	return $psi;
}

function load_database_file($dbtype, $dbhost, $dbname, $dbuser, $dbpass, $dbfile)
{
	system("$dbtype -h $dbhost -u $dbuser -p$dbpass $dbname < $dbfile");
}

function do_serve_file($fd, $rem)
{
	$file = $rem->filename;

	$file = basename($file);
	$file = "__path_serverfile/$file";

	if (!lxfile_exists($file)) {
		log_log("servfile", "datafile $file dosn't exist, exiting");
		print_or_write($fd, "fFile Doesn't $file Exist...\n\n\n\n");
		return false;
	}

	$array = lfile_get_unserialize($file);
	lunlink($file);
	$realfile = $array['filename'];
	$pass = $array['password'];

	if ($fd) {
		dprint("Got request for $file, realfile: $realfile\n");
	}

	log_log("servfile", "Got request for $file realfile $realfile");
	if (!($pass && $pass === $rem->password)) {
		print_or_write($fd, "fPassword doesn't match\n\n");
		return false;
	}

	if (is_dir($realfile)) {
		// This should neverhappen. The directories are zipped at cp-fileserv and tar_to_filserved then itself.
		$b = basename($realfile);
		lxfile_mkdir("__path_serverfile/tmp/");
		$tfile = tempnam("__path_serverfile/tmp/", "$b.tar");
		$list = lscandir_without_dot($realfile);
		lxshell_tar($realfile, $tfile, $list);
		$realfile = $tfile;
	}

	$fpr = lfopen($realfile, "rb");

	if (!$fpr) {
		print_or_write($fd, "fCouldn't open $realfile\n\n");
		return false;
	}

	print_or_write($fd, "s");

	while(!feof($fpr)) {
		$written = print_or_write($fd, fread($fpr, 8092));
		if ($written <= 0) {
			break;
		}
	}

	// Just send a newline so that the fgets will break after reading. This has to be removed after the file is read.
	print_or_write($fd, "\n");

	fclose($fpr);

	fileserv_unlink_if_tmp($realfile);

	return true;

}

function notify_admin($action, $parent, $child)
{
	$cclass = $child->get__table();
	$cname = $child->nname;
	$pclass = $parent->getClass();
	$pname = $parent->nname;

	$not = new notification(null, null, 'client-admin');
	$not->get();

	if (!array_search_bool($cclass, $not->class_list)) {
		return;
	}
	$subject = "$cclass $cname was $action to $pclass $pname ";
	send_mail_to_admin($subject, $subject);
}

function trafficGetIndividualObjectTotal($list, $firstofmonth, $today, $name) 
{
	
	$tot = 0;

	foreach((array) $list as $t) {

		//if (!(csa($t->timestamp, "Aug") && csa($t->timestamp, "2007"))) {
			//continue;
		//}

		list($nname, $oldtime, $newtime) = explode(":", $t->nname);
		//dprint("$oldtime:$newtime: $firstofmonth: $t->timestamp $today\n");

		if($oldtime >= $firstofmonth && $oldtime < $today) {
			dprint(@ strftime("%c" , "$oldtime"). ": ");
			dprint($t->traffic_usage);
			dprint("\n");
			$tot +=  $t->traffic_usage;
		}
	}

	return $tot;
}

function get_last_month_and_year()
{
	$month = @ date("n");
	$year = @ date("Y");
	if ($month == 1) {
		$month = 12;
		$year = $year - 1; 
	} else {
		$month = $month - 1;
		$year = $year;
	}
	return array($month, $year);
}

function add_to_log($file)
{
	$string = time();
	$d = @ date("Y-M-d H:i");
	$string = "$string $d __lxlabs_marker\n";
	lfile_put_contents($file, $string, FILE_APPEND);
}

function findServerTraffic()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$sq = new Sqlite(null, 'vps');
	$list = $login->getList('pserver');
	foreach($list as $l) {
		$res = $sq->getRowsWhere("syncserver = '$l->nname'", array('used_q_traffic_usage', 'used_q_traffic_last_usage'));
		$tusage = 0;
		$tlastusage = 0;
		foreach($res as $r) {
			$tusage += $r['used_q_traffic_usage'];
			$tlastusage += $r['used_q_traffic_last_usage'];
		}
		$l->used->server_traffic_usage = $tusage;
		$l->used->server_traffic_last_usage = $tlastusage;
		$l->setUpdateSubaction();
		$l->write();
	}

}

function createMultipLeVps($param)
{
	$adminpass = $param['vps_admin_password_f'];
	$template = $param['vps_template_name_f'];
	$one_ip = $param['vps_one_ipaddress_f'];
	$base = $param['vps_basename_f'];
	$count = $param['vps_count_f'];
	lxshell_background("__path_php_path", "../bin/multicreate.php", "--admin-password=$adminpass", "--v-template_name=$template", "--count=$count", "--basename=$base", "--v-one_ipaddress=$one_ip");
}

function collect_quota_later()
{
	createRestartFile("lxcollectquota");
}

function exec_justdb_collectquota()
{
	lxshell_background("__path_php_path", "../bin/collectquota.php", "--just-db=true");
}

function setup_ssh_channel($source, $destination, $actualname)
{
	$cont = rl_exec_get(null, $source, "get_scpid", array());
	$cont = rl_exec_get(null, $destination, "setup_scpid", array($cont));
	$cont = rl_exec_get(null, $source, "setup_knownhosts", array("$actualname, $cont"));
}

function exec_vzmigrate($vpsid, $newserver, $ssh_port)
{
	global $global_shell_out, $global_shell_error, $global_shell_ret;

	//$ret = lxshell_return("vzmigrate", "--ssh=\"-p $ssh_port\"", "-r", "yes", $newserver, $vpsid);
	$username = '__system__';

	$ssh_port = trim($ssh_port);
	$ssh_string = null;
	if ($ssh_port !== "22")  {
		$ssh_string = "--ssh=\"-p $ssh_port\"";
	}
	//do_exec_system($username, null, "vzmigrate --online $ssh_string -r yes $newserver $vpsid", $out, $err, $ret, null);
	do_exec_system($username, null, "vzmigrate $ssh_string -r yes $newserver $vpsid", $out, $err, $ret, null);
	return array($ret, $global_shell_error);
}

function getResourceOstemplate(&$vlist, $ttype = 'all')
{
	$olist = vps::getVpsOsimage(null, "openvz");
	$olist = array_keys($olist);
	$xlist = vps::getVpsOsimage(null, "xen");
	$xlist = array_keys($xlist);
	if ($ttype === 'openvz' || $ttype === 'all') {
		$vlist['openvzostemplate_list'] = array('U', $olist);
	}
	if ($ttype === 'xen' || $ttype === 'all') {
		$vlist['xenostemplate_list'] = array('U', $xlist);
	}
}

function get_scpid()
{
	$home = os_get_home_dir("root");
	$file = "$home/.ssh/id_dsa";
	if (!lxfile_exists($file)) {
		lxshell_return("ssh-keygen", "-d", "-q", "-N", null, "-f", $file);
	}
	return lfile_get_contents("$file.pub");
}

function setup_knownhosts($cont)
{
	$home = os_get_home_dir("root");
	lfile_put_contents("$home/.ssh/known_hosts", "$cont\n", FILE_APPEND);
}

function setup_scpid($cont)
{
	global $global_dontlogshell;
	$global_dontlogshell = true;
	$home = os_get_home_dir("root");
	$file = "$home/.ssh/authorized_keys2";

	lxfile_mkdir("$home/.ssh");
	lxfile_unix_chmod("$home/.ssh", "0700");
	addLineIfNotExistInside($file, "\n$cont", '');
	lxfile_unix_chmod($file, "0700");
	$global_dontlogshell = false;
	return lfile_get_contents("/etc/ssh/ssh_host_rsa_key.pub");
}

function remove_scpid($cont)
{
	$home = os_get_home_dir("root");
	$file = "$home/.ssh/authorized_keys2";
	$list = lfile_trim($file);
	foreach($list as $l) {
		if (!$l) continue;
		if ($l === $cont) {
			continue;
		}
		$nlist[] = $l;
	}

	lfile_put_contents($file, implode("\n", $nlist) . "\n");
}

function lxguard_clear($list)
{

}

function lxguard_main($clearflag = false)
{
	include_once "htmllib/lib/lxguardincludelib.php";

	lxfile_mkdir("__path_home_root/lxguard");
	$lxgpath = "__path_home_root/lxguard";


	$file = "/var/log/secure";
	$fp = fopen($file, "r");
	$fsize = filesize($file);
	$newtime = time();
	$oldtime = time() - 60 * 10;
	$rmt = lfile_get_unserialize("$lxgpath/hitlist.info");
	if ($rmt) { $oldtime =  max((int) $oldtime, (int) $rmt->ddate); }
	$ret = FindRightPosition($fp, $fsize, $oldtime, $newtime, "getTimeFromSysLogString");

	$list = lfile_get_unserialize("$lxgpath/access.info");

	if ($ret) { 
		parse_sshd_and_ftpd($fp, $list);
		lfile_put_serialize("$lxgpath/access.info", $list);
	}

	get_total($list, $total);

	//dprintr($list['192.168.1.11']);

	dprint_r("Debug: Total: " . $total .  "\n");
	$deny = get_deny_list($total);
	$hdn = lfile_get_unserialize("$lxgpath/hostdeny.info");
	$deny = lx_array_merge(array($deny, $hdn));
	$string = null;
	foreach($deny as $k => $v) {
		if (csb($k, "127")) {
			continue;
		}
		$string .= "ALL : $k\n";
	}

	dprint("Debug: \$string is:\n" . $string .  "\n");

	$stlist[] = "###Start Program Hostdeny config Area";
	$stlist[] = "###Start Lxdmin Area";
	$stlist[] = "###Start Kloxo Area";
	$stlist[] = "###Start Lxadmin Area";

	$endlist[] = "###End Program HostDeny config Area";
	$endlist[] = "###End Kloxo Area";
	$endlist[] = "###End Lxadmin Area";

	$startstring = $stlist[0];
	$endstring = $endlist[0];

	file_put_between_comments("root",$stlist, $endlist, $startstring, $endstring, "/etc/hosts.deny", $string);

	if ($clearflag) {
		lxfile_rm("$lxgpath/access.info");
		$rmt = new Remote();
		$rmt->hl = $total;
		$rmt->ddate = time();
		lfile_put_serialize("$lxgpath/hitlist.info", $rmt);
	}
	return $list;
}

function lxguard_save_hitlist($hl)
{
	include_once "htmllib/lib/lxguardincludelib.php";

	lxfile_mkdir("__path_home_root/lxguard");
	$lxgpath = "__path_home_root/lxguard";
	$rmt = new Remote();
	$rmt->hl = $hl;
	$rmt->ddate = time();
	lfile_put_serialize("$lxgpath/hitlist.info", $rmt);
	lxguard_main();
}

