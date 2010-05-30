<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');
init_language();

if (!isset($argv[2])) {
	print("Usage: reflect --type=add --parent-class= --parent-name= --class= [--v-var= --v-val=]\n");
	print("Usage: reflect --type=update --subaction= --class= --name= \n\n");
	print("Example: How to add a customer to admin:\n");
	print("/script/reflect --type=add --parent-class=client --parent-name=admin --class=client --v-var=ttype --v-val=customer\n\n");
	print("Example: How to add a wordpress to a domain domain.com:\n");
	print("/script/reflect --type=add --parent-class=web --parent-name=domain.com --class=installapp --v-var=appname --v-val=wordpress\n");
	exit;
}


$list = parse_opt($argv);
$class = $list['class'];
$type = $list['type'];

if ($type === 'update') {
	$subaction = $list['subaction'];
	$object = new $class(null, null, $list['name']);
	$object->get();
	if ($object->dbaction === 'add') {
		print("object {$object->getClName()} doesn't exist\n");
		exit;
	}
	$param = null;
	$param = $object->updateform($subaction, $param);

} else if ($type === 'add') {
	$pc = $list['parent-class'];
	$pn = $list['parent-name'];
	$parent = new $pc(null, null, $pn);
	$parent->get();
	if ($parent->dbaction === 'add') {
		print("parent {$parent->getClName()} doesn't exist\n");
		exit;
	}
	$typtd = null;
	if (isset($list['v-val'])) { $typtd['val'] = $list['v-val']; }
	if (isset($list['v-var'])) { $typtd['var'] = $list['v-var']; }
	$parent->priv = new Priv(null, null, $pn);
	$param = exec_class_method($class, 'addform', $parent, $class, $typtd);
	$param = $param['variable'];
} else {
	printProperty($class, $type);
}

foreach($param as $k => $v) {
	if (csb($k, "__v") || csb($k, "__c") || csb($k, "__m")) { continue; }
	$desc = get_classvar_description($class, $k);
	$c = null;
	$prep = null;
	if ($v && is_array($v)) {
		if ($v[0] === 'M') { 
			$c = "Static: {$v[1]}";
		}
	}
	if (csa($desc[0], 'q')) {
		$k = "priv-$k";
	}

	printf("%40s %s $c\n", $k, $desc[2]);
}



function printProperty($class, $type)
{
	$r = new ReflectionClass($class);
	foreach($r->getProperties() as $s) {
		if ($type === 'action') {
			$istr = "__acdesc_";
			if (!csb($s->name, "__acdesc_"))
				continue;
		}
		if ($type === 'property') {
			$istr = "__desc_";
			if (!csb($s->name, "__desc_"))
				continue;
		}

		$descr = get_classvar_description($class, $s->name);
		$name = strfrom($s->name, $istr);
		if (csa($descr[0], "q")) { continue; }
		if (cse($name, "_f")) {continue; }
		if (cse($name, "_l")) {continue; }
		if (cse($name, "_o")) {continue; }
		printf("%30s %s\n", $name, $descr['help']);

	}
}

