<?php

class DomaintrafficHistory extends TrafficHistory
{
	static $__desc_ftptraffic_usage  = array('', '', 'ftp_traffic_(MB)');
	static $__desc_webtraffic_usage  = array('', '', 'web_(MB)');
	static $__desc_mailtraffic_usage = array('', '', 'mail_(MB)');

	static function createListNlist($parent, $view)
	{
		$nlist['month']             = '100%';
		$nlist['ftptraffic_usage']  = '50%';
		$nlist['mailtraffic_usage'] = '50%';
		$nlist['webtraffic_usage']  = '50%';
		$nlist['traffic_usage']     = '50%';
		return $nlist;
	}

	function isSync()
	{
		return false;
	}

	static function initThisList($parent, $class)
	{
		$result = self::getTrafficMonthly($parent, 'domaintraffic', self::getExtraVar());
		return $result;
	}

	static function getExtraVar()
	{
		return array('ftptraffic_usage', 'webtraffic_usage', 'mailtraffic_usage');
	}

}
