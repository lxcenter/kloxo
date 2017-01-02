<?php 
class serverinfo extends lxclass {

	static $__desc = array("", "", "serverinfo");
	static $__desc_nname = array("", "", "serverinfo");
	static $__acdesc_show = array("", "",  "serverinfo");
	static $__acdesc_show_serverinfo = array("", "",  "serverinfo");
	
	function get() {}
	function write() {}
	
	function showRawPrint($subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$driverapp = $gbl->getSyncClass($this->__masterserver, $this->__readserver, 'pserver');
		if($driverapp == 'linux')
		{
			$loadinfo = array(
			'sysinfo' => 'System Information',
			'cpuinfo' => 'CPU Information',
			'meminfo' => 'Memory Information',
			'diskinfo' => 'Current Disk Usage');
			$info = array();
			foreach($loadinfo as $drive => $title)
			{
				$info[$drive] = rl_exec_get($this->__masterserver, $this->__readserver,  array("serverinfo__$driverapp", $drive), array($login->isAdmin()));
				$html = lfile_get_contents("htmllib/filecore/serverinfo-table.html");
				$html = str_replace("%title%", $title, $html);
				$html = str_replace("%content%", $info[$drive], $html) and print("<br />");
				$ghtml->print_curvy_table_start("100");
				print($html);
				$ghtml->print_curvy_table_end("100");
			}
		} else {
			throw new lxexception("system_driver_is_not_linux", null, $this->nname);
		}
	}
	
	static function initThisObjectRule($parent, $class) { return "serverinfo"; }
	static function initThisObject($parent, $class, $name = null) { return "serverinfo"; }
}
