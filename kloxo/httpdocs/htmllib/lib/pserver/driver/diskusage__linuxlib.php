<?php 

class DiskUsage__Linux extends lxDriverClass {

static function getDiskUsage()
{
	$cont = lxshell_output("df", "-P");
	$result = self::parseDiskUsage($cont);
	return $result;
}


static function parseDiskUsage($cont)
{
	$cont = preg_replace('/\n+/i' , "\n" , $cont);
	$arr = explode("\n", $cont);
	$i = 0;
	foreach($arr as $a) {
		if (!$i) {
			$i++;
			continue;
		}
		$a = preg_replace('/\s+/i' , " " , $a);
		$r = explode(' ', $a);

		if (!$r[0]) {
			continue;
		}
		$result[$i]['nname'] = $r[0];
		$result[$i]['kblock'] = round($r[1]/1000);
		$result[$i]['available'] = round($r[3]/1000);
		$result[$i]['used'] = $result[$i]['kblock'] - $result[$i]['available'];
		$result[$i]['pused'] = str_replace("%", "", $r[4]);
		$result[$i]['mountedon'] = $r[5];
		$i++;
	}
	return $result;
}

}
