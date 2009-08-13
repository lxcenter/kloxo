<?php 

include "htmllib/lib/include.php";

// Dynamically create language files.

description_main();

function description_main()
{
	global $gbl, $sgbl, $login, $ghtml; 

	global $gl_class_array;

	foreach($gl_class_array as $k => $g) {
		if (csa($k, "__")) {
			continue;
		} 

		if (cse($k, "base") || cse($k, "core")) {
			continue;
		} 

		try {
			$r = new ReflectionClass($k);
		} catch (exception $e) {
			continue;
		}
		if ($r->isAbstract()) {
			continue;
		}
		$ob = new $k(null, null, "name", null, null);
	}

	system("mkdir -p lang/en");
	system("rm lang/en/*");

	$list = get_declared_classes();

	foreach($list as $k => $v) {

		$class = $v;
		try {
			$r = new ReflectionClass($class);
		} catch (exception $e) {
			continue;
		}
		if ($r->isAbstract()) {
			continue;
		}
		// First pass to isolate teh _v_ variable
		foreach($r->getProperties() as $s) {
			if (!csb($s->name, "__desc") && !csb($s->name, "__acdesc")) continue;
			$descr = get_real_class_variable($class, $s->name);
			$name = $s->name;
			$v = strtolower($v);
			$name = strtolower($name);
			$ret[$v][$name]  = $descr;

		}
	}

	$str = "<?php \n";
	foreach($ret as $k => $v) {
		foreach($v as $nk => $nv) {
			/* Let the definitions be made multiple times, but it is better to have them rather than not have them... So the line below is not needed.
			if ($k != 'lxclass' && isset($ret['lxclass'][$nk])) {
				continue;
			}
		*/
			if (cse($nk, "_o") || cse($nk, "_l")) {
				continue;
			}
			if (!isset($nv[2])) {
				dprint("2: $k  $nk \n");
				continue;
			}
			if (csb($nv[2], "__k_")) {
				continue;
			}
			$k = trim($nv[2], "_\n ");
			$description[$k] = change_underscore($nv[2]);

		}
	}

	foreach($description as $k => $v) {
		$str .= "\$__description[\"$k\"] = array(\"$v\");\n";
	}
	
	$str .= "\n";
	file_put_contents("lang/en/desclib.php", $str);

	include_once "htmllib/lib/messagelib.php";
	include_once "lib/messagelib.php";

	$string = "<?php\n";
	foreach($__information as $k => $v) { $string .= "\$__information['$k'] = \"$v\";\n"; }
	foreach($__emessage as $k => $v) { $string .= "\$__emessage['$k'] = \"$v\";\n"; }
	//foreach($__smessage as $k => $v) { $string .= "\$__smessage['$k'] = \"$v\";\n"; }

	lfile_put_contents("lang/en/messagelib.php", $string);

	copy("htmllib/lib/langfunctionlib.php", "lang/en/langfunctionlib.php");
	copy("htmllib/lib/langkeywordlib.php", "lang/en/langkeywordlib.php");
	system("mkdir -p help/");
	system("cp htmllib/help-core/* help/");
	system("cp help-base/* help/");

	

}

