<?php 

include_once "htmllib/lib/include.php"; 

$list = lscandir_without_dot_or_underscore("/home/kloxo/httpd/ssl/");

foreach($list as $l) {
	if (cse($l, ".crt")) {
		$newlist[] = basename($l, ".crt");
	} else {
		continue;
	}
}

foreach($newlist as $n) {
	lxfile_cp("/usr/local/lxlabs/kloxo/file/program.crt", "/home/kloxo/httpd/ssl/$n.crt");
	lxfile_cp("/usr/local/lxlabs/kloxo/file/program.key", "/home/kloxo/httpd/ssl/$n.key");
	lxfile_cp("/usr/local/lxlabs/lxadmin/httpdocs/htmllib/filecore/program.ca", "/home/kloxo/httpd/ssl/$n.ca");
}
