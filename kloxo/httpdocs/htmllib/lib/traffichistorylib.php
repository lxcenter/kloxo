<?php 

class TrafficHistory extends lxclass {

static $__desc =  Array("", "",  "traffic_history");
static $__desc_nname =  Array("", "",  "device_name");
static $__desc_parent_name =  Array("", "",  "device_name");
static $__desc_month    =   Array("", "",  "month");
static $__desc_traffic_usage     =  Array("", "",  "total_traffic_(MB)");
static $__acdesc_list     =  Array("", "",  "traffic_history");

//Objects

//Lists

function write() {}
function get() {}


function isSelect()
{
	return false;
}
static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}

function getTrafficRealObject()
{
	return strtil($this->get__table(), "traffic");
}

function getAllTrafficMonthly() 
{

	global $gbl, $sgbl, $login, $ghtml; 

	$robjname = $this->getTrafficRealObject();
	$login->loadAllObjects($robjname);

	$domlist = $login->getList($robjname);

	foreach($objlist as $obj) {
		$trafficlist[$obj->nname] = self::getTrafficMonthly($obj);
	}
	
	return $trafficlist;
}	

static function defaultSortdir() { return 'desc'; }

static function getTrafficMonthly($object, $trafficname, $extra_var)  
{
	
	$tobjectlist  = $object->getList($trafficname);

	if (!$tobjectlist) {
		return null;
	}
	$list1 = get_namelist_from_objectlist($tobjectlist);
	$list = lx_array_keys($list1);
	list(, $start, ) = explode( ':', $list[0]); 
	$count = count($list);
	list( , , $end ) = explode(':', $list[$count-1]);
	$smonth = @ strftime("%m", $start);
	$emonth = @ strftime("%m", $end);
	$name = $object->nname;
  

	$thmonth = @ date("n");
	$year = @ date("Y");
	$count = 0;
	for($i = $thmonth ; $i != ($thmonth + 1);) {
		$count++;
		if ($count > 14) { break; }

		$totallist[] = self::getMonthTotal($tobjectlist, $i, $year, $extra_var); 
		if ($i == 1) {
			$i = 13;
			$year = $year - 1;
		}
		$i--;

	}

	return $totallist;
}

static function getMonthTotal($list, $month, $year, $extra_var) 
{

	$tot = 0;
	foreach((array) $extra_var as $v) {
		$res[$v] = 0;
	}
	$nname = '1110';
	foreach((array) $list as $t ) {
		list($domname, $oldtime, $newtime) = explode( ":", $t->nname);
		$cmonth = @ strftime("%m" , $oldtime);
		//dprint(strftime("%c" , "$oldtime"). ": "); dprint($t->traffic_usage); dprint("$cmonth <br> \n");
		//		dprint("$cmonth $month $t->traffic_usage <br>");
		$yy = @ date("Y", $oldtime);
		if ($yy != $year) {
			continue;
		}

		if($cmonth == $month) {
			$tot +=  $t->traffic_usage;
			foreach((array) $extra_var as $v) {
				$res[$v] += $t->$v;
			}
			$nname = $oldtime;
		}
	}
	$montht = intToMonth($month);
	$res['nname'] = "$year.$month";
	$res['month'] = "$montht $year";
	$res['traffic_usage'] = $tot;

	return $res;
	 
}

}
