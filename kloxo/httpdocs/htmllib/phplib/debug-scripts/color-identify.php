<?php 

include_once "htmllib/lib/include.php";

color_main();



function read_rgb()
{
	$list = file("htmllib/phplib/debug-scripts/color.txt");
	foreach($list as $l) {
		$l = trim($l);
		$l = preg_replace("/\s+/", " " , $l);
		$color[] = explode(' ', $l);
	}
	foreach($color as &$c) {
		$nl = null;
		if (isset($c[4]) && $c[4]) {
			$nl[] = $c[3];
			$nl[] = $c[4];
			$c[3] = implode('_', $nl);
			unset($c[4]);
		}
	}
	return $color;
}

function color_filter_list($c, $list, $n) 
{

	$sel = null;
	foreach($list as $l) {
		$match = 1;
		for($k = 0; $k < 3; $k++) {
			$low = $l[$k] - $n;
			$high = $l[$k] + $n;
			if ($c[$k] <= $low || $c[$k] >= $high) {
				$match = 0;
				break;
			}
		}
		if (!$match) {
			continue;
		}

		$sel[] = $l;
	}
	return $sel;
}

function get_color($img)
{
	global $gbl, $sgbl, $login, $ghtml;

	$l = `identify -verbose $img`;
	$linelist = explode("\n", $l);
	$reachedred = $reachedgreen = $reachedblue = false; 


	foreach($linelist as $l) {
		if (csa($l, "Red:")) {
			$reachedred = 1;
			continue;
		}
		if (csa($l, "Green:")) {
			$reachedgreen = 1;
			continue;
		}
		if (csa($l, "Blue:")) {
			$reachedblue = 1;
			continue;
		}
		
		if ($reachedblue || $reachedgreen || $reachedred) {
			if (csa($l, "Min:")) {
				preg_match("/Min: ([^ ]*).*/", $l, $match);
				if ($reachedred) {
					$res[0] = $match[1];
					$reachedred = false;
				}
				if ($reachedgreen) {
					$res[1] = $match[1];
					$reachedgreen = false;
				}
				
				if ($reachedblue) {
					$res[2] = $match[1];
					$reachedblue = false;
				}
			}
		}
	}

	return $res;
}

function color_main()
{
	global $gbl, $sgbl, $login, $ghtml, $argv, $argc; 

	$color = read_rgb();

	$list = scandir($argv[1]);

	foreach($list as $l) {
		if (!csa($l, "skin") || !is_dir("$argv[1]/$l")) {


			print("sking.. $l\n");
			continue;
		}
		if (!file_exists($argv[1] . "/" . $l . '/top_line_light.gif')) {
			print("no image.. Skipping $l... \n");
			continue;
		}

		print("\nWorking on .... $l");
		flush();


		$m1 = get_color("$argv[1]/$l/top_line_light.gif");
		$m2 = get_color("$argv[1]/$l/top_line_medium.gif");
		$m3 = get_color("$argv[1]/$l/top_line.gif");


		for($k = 0; $k< 3; $k++) {
			$m[$k] = round(($m1[$k]  + $m2[$k] + $m2[$k] ) / 3);
		}

		$r = color_identify($m, $color);
		for($k = 0; $k < 3; $k++) {
			$m[$k] = dechex($m[$k]);
		}
		for($k = 0; $k < 3; $k++) {
			$m1[$k] = dechex($m1[$k]);
		}
		for($k = 0; $k < 3; $k++) {
			$m2[$k] = dechex($m2[$k]);
		}
		for($k = 0; $k < 3; $k++) {
			$m4[$k] = dechex(max($m3[$k] - 50, 0));
		}
		for($k = 0; $k < 3; $k++) {
			$m3[$k] = dechex($m3[$k]);
		}

		$nname = "$r[3]_$m[0]$m[1]$m[2]";
		system("(cd {$argv[1]} ; rm -rf $nname ; cp -a $l $nname)"); 
		$linelist = file("htmllib/phplib/debug-scripts/template.css");
		foreach($linelist as &$l) {
			$l = str_replace("[%verydark]", "#" . implode("", $m4), $l); 
			$l = str_replace("[%dark]", "#" . implode("", $m3), $l); 
			$l = str_replace("[%medium]", "#" . implode("", $m2), $l); 
			$l = str_replace("[%light]", "#" . implode("", $m1), $l); 
		}

		$ll = implode('', $linelist);
		file_put_contents("{$argv[1]}/$nname/css.css", $ll);
		file_put_contents("{$argv[1]}/$nname/base_color", implode("", $m2));
	}
}



function color_identify($match, $color)
{
	for($q = 0 ; $q < 256; $q++) {
		$res = color_filter_list($match, $color, $q);
		if (count($res)) {
			break;
		}
	}
	$r = $res[0];
	return $r;
}
