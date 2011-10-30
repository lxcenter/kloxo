<?php 

class serverweb__lighttpd extends lxDriverClass {

function dbactionUpdate($subaction)
{
	// issue #571, #566, #567 - also mysql-convert and fix-chownchmod for lighttpd
	
	$m = $this->main->mysql_convert;
	$f = $this->main->fix_chownchmod;

	if ($f === 'fix-ownership') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php --select=chown");
	//	setFixChownChmod('chown');
	}
	else if ($f === 'fix-permissions') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php --select=chmod");
	//	setFixChownChmod('chmod');
	}
	else if ($f === 'fix-ALL') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php --select=all");
	//	setFixChownChmod('all');
	}
	
	if ($m === 'to-myisam') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/mysql-convert.php --engine=myisam");
	//	setMysqlConvert('myisam');
	}
	else if ($m === 'to-innodb') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/mysql-convert.php --engine=innodb");
	//	setMysqlConvert('innodb');
	}
}

}

