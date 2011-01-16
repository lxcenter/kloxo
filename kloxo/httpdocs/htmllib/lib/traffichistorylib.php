<?php 

class TrafficHistory extends Lxclass
{
	static $__desc               = array('', '', 'traffic_history');
	static $__desc_nname         = array('', '', 'device_name');
	static $__desc_parent_name   = array('', '', 'device_name');
	static $__desc_month         = array('', '', 'month');
	static $__desc_traffic_usage = array('', '', 'total_traffic_(MB)');
	static $__acdesc_list        = array('', '', 'traffic_history');

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

	static function createListBlist($parent, $class)
	{
		return null;
	}

	static function defaultSort()
	{
		return 'month';
	}

	static function defaultSortDir()
	{
		return 'desc';
	}

	function display($var)
	{
		if ($var == 'month') {
			$month = date('n', $this->$var);
			$month = intToMonth($month);
			$year = date('Y', $this->$var);
			return "$year $month";
		}

		return parent::display($var);
	}

	function getTrafficRealObject()
	{
		return strtil($this->get__table(), 'traffic');
	}

	function getAllTrafficMonthly()
	{
		global $login;

		$robjname = $this->getTrafficRealObject();
		$login->loadAllObjects($robjname);

		$domlist = $login->getList($robjname);

		foreach($domlist as $obj) {
			$trafficlist[$obj->nname] = self::getTrafficMonthly($obj);
		}

		return $trafficlist;
	}

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

		$month = @ date("n");
		$year = @ date("Y");
		$count = 0;
		while ($count < 13) {
			$totallist[] = self::getMonthTotal($tobjectlist, $month, $year, $extra_var);
			if ($month == 1) {
				$month = 12;
				$year--;
			} else {
				$month--;
			}
			$count++;
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

		$timestamp = mktime(0, 0, 0, $month, 1, $year);
		$month = intToMonth($month);
		
		$res['nname'] = "$year $month";
		$res['month'] = $timestamp;
		$res['traffic_usage'] = $tot;

		return $res;
	}

}
