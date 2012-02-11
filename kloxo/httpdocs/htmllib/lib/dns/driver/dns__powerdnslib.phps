<?PHP
/*
*
*
* PowerDNS driver for Kloxo.
* 10:31 AM 8/24/2007 Ahmet YAZICI ahmet.yazici@pusula.net.tr
*
*  Usage : get and install powerdns from www.powerdns.com
*  Create your database and import powerdns schema..
*  Let kloxo to use powerdns as default dns driver via
*  cd /usr/local/lxlabs/kloxo/httpdocs/
*  lphp.exe ../bin/common/setdriver.php --server=localhost --class=dns --driver=powerdns
*
*  Changelog :
*  01:07 AM 8/26/2007 Ahmet 
*     Moved sql variables to secure location 
*	
*/


class dns__powerdns extends lxDriverClass {

    function dbactionUpdate($subaction) 
    { 
	$this->dbactionDelete();
	$this->dbactionAdd();
    }

    function dbConnect()
    {

	include_once "/usr/local/lxlabs/kloxo/etc/powerdns.conf.inc";
	mysql_connect($power_sql_host,$power_sql_user,$power_sql_pwd);
	mysql_select_db($power_sql_db);

    }

    function dbClose() 
    {
	@mysql_close();
    }

    function dbactionAdd()
    {
	$this->dbConnect();

		$domainname = $this->main->nname;
		mysql_query("INSERT INTO domains (name,type) values('$domainname','NATIVE')");

		if(mysql_affected_rows()) {
			$this_domain_id = mysql_insert_id();

			foreach($this->main->dns_record_a as $k => $o) {
				switch($o->ttype) {
					case "ns":
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$domainname','$o->param','NS','3600','NULL')");
						break;
					case "mx":
						$v = $o->priority;
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$domainname','$o->param','MX','3600','$v')");
						break;
					case "a":
						$key = $o->hostname;
						$value = $o->param;
						if ($key === '*') {
							$starvalue = "* IN A $value";
							break;
						}
						if ($key !== "__base__") {
							$key = "$key.$domainname";
						} else {
							$key = "$domainname";
						}

						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$key','$value','A','3600','NULL')");

						break;
					case "cn":
					case "cname":
						$key = $o->hostname;
						$value = $o->param;
						$key .= ".$domainname";

						if ($value !== "__base__") {
							$value = "$value.$domainname";
						} else {
							$value = "$domainname";
						}

						if ($key === '*') {
							$starvalue = "*		IN CNAME $value\n";
							break;
						}
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$key','$value','CNAME','3600','NULL')");
						break;

					case "fcname":
						$key = $o->hostname;
						$value = $o->param;
						$key .= ".$domainname";

						if ($value !== "__base__") {
							if (!cse($value, ".")) {
								$value = "$value.";
							}
						} else {
							$value = "$domainname";
						}

						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$key','$value','CNAME','3600','NULL')");
						break;

					case "txt":
						$key = $o->hostname;
						$value = $o->param;
						if($o->param === null) continue;	

						if ($key !== "__base__") {
							$key = "$key.$domainname.";
						} else {
							$key = "$domainname.";
						}

						$value = str_replace("<%domain>", $domainname, $value);
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$key','$value','TXT','3600','NULL')");

						break;
				}
			}
			
		}
			

	$this->dbClose();
   }


	function dbactionDelete()
	{
		$this->dbConnect();		
		$this_domain =  $this->main->nname;
		$my_query = mysql_query("SELECT * FROM domains WHERE name='".$this_domain."'");
		if (mysql_num_rows($my_query)){
			$this_row = mysql_fetch_object($my_query);
			$this_domain_id = $this_row->id;
		
			@mysql_query("DELETE FROM domains WHERE id='".$this_domain_id."'");
			@mysql_query("DELETE FROM records WHERE domain_id='".$this_domain_id."'");
			
		}

		$this->dbClose();
	}

	function dosyncToSystemPost()
	{
		global $sgbl;

	}

}

