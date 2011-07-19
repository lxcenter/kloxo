<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$innodb = $list['innodb'];

$pass = slave_get_db_pass();

//-- use system() rather then exec() if want return value
$ret = exec("mysql --user=root --password={$pass} --execute=\"SHOW VARIABLES LIKE 'have_innodb'\" | grep YES");

if ($innodb === 'status') {
	if ($ret === '') {
		echo "InnoDB disabled\n";
	}
	else {
		echo "InnoDB enabled\n";
	}
}
else if ($innodb === 'skip') {
	if ($ret !== '') {
		$a = "[mysqld]\n";
		$b = "[mysqld]\nskip-innodb\n";

		$s=implode("", file("/etc/my.cnf"));
		$f = fopen("/etc/my.cnf", "w");
		$s = str_replace($a, $b, $s);
		fwrite($f,$s,strlen($s));

		exec("service mysqld restart");
	}
}


