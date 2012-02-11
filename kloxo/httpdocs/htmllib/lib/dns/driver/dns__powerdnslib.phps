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
<<<<<<< HEAD
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
=======
*     Moved sql variables to secure location
*  2010-06-08 Lars Nordseth (LN)
*     Replaced include_once with require in dbConnect
*     dbactionAdd generates SOA record
*     SOA record is generated with defaults or new user settings if available
*     Removed single quotes on numeric values in SQL statements
*     Removed redundant null values
*		Added support for DDNS record
*		Added support for SRV record
*	
*/

class dns__powerdns extends lxDriverClass
{
   const DEFAULT_REFRESH = 3600; // 1 hour
   const DEFAULT_RETRY   = 1800; // 30 minutes
   const DEFAULT_EXPIRE  = 604800; // 1 week
   const DEFAULT_MINIMUM = 1800; // 30 minutes
    
   function dbactionUpdate($subaction)
   {
      $this->dbactionDelete();
      $this->dbactionAdd();
   }	

   function dbConnect()
   {
      require("/usr/local/lxlabs/kloxo/etc/powerdns.conf.inc");
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
      $defaultTtl = $this->main->ttl;
      mysql_query("INSERT INTO domains (name,type) values('$domainname','NATIVE')");

		$email = isset($this->main->email) && strlen($this->main->email) > 0 ? str_replace("@", ".", $this->main->email) : $this->main->__var_email;
      $refresh = isset($this->main->refresh) && strlen($this->main->refresh) > 0 ? $this->main->refresh : self::DEFAULT_REFRESH;
      $retry = isset($this->main->retry) && strlen($this->main->retry) > 0 ? $this->main->retry : self::DEFAULT_RETRY;
      $expire = isset($this->main->expire) && strlen($this->main->expire) > 0 ? $this->main->expire : self::DEFAULT_EXPIRE;
      $minimum = isset($this->main->minimum) && strlen($this->main->minimum) > 0 ? $this->main->minimum : self::DEFAULT_MINIMUM;

      if(mysql_affected_rows()) {
         $this_domain_id = mysql_insert_id();
         mysql_query("INSERT INTO records (domain_id, name, content, type,ttl) VALUES ($this_domain_id,'$domainname','{$this->main->soanameserver} $email {$this->main->__var_ddate} $refresh $retry $expire $minimum','SOA',$defaultTtl)");
         foreach($this->main->dns_record_a as $k => $o) {
            $ttl = isset($o->ttl) && strlen($o->ttl) ? $o->ttl : $this->main->ttl;
            switch($o->ttype) {
               case "ns":
                  mysql_query("INSERT INTO records (domain_id, name, content, type,ttl) VALUES ($this_domain_id,'$domainname','$o->param','NS',$ttl)");
                  break;
               case "mx":
                  $v = $o->priority;
                  mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ($this_domain_id,'$domainname','$o->param','MX',$ttl,'$v')");
                  break;
					case "ddns":
						if ($o->offline === 'on')
							break;
               case "a":
>>>>>>> upstream/dev
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

<<<<<<< HEAD
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$key','$value','A','3600','NULL')");
=======
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl) VALUES ($this_domain_id,'$key','$value','A',$ttl)");
>>>>>>> upstream/dev

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
<<<<<<< HEAD
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$key','$value','CNAME','3600','NULL')");
=======
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl) VALUES ($this_domain_id,'$key','$value','CNAME',$ttl)");
>>>>>>> upstream/dev
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

<<<<<<< HEAD
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$key','$value','CNAME','3600','NULL')");
=======
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl) VALUES ($this_domain_id,'$key','$value','CNAME',$ttl)");
>>>>>>> upstream/dev
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
<<<<<<< HEAD
						mysql_query("INSERT INTO records (domain_id, name, content, type,ttl,prio) VALUES ('$this_domain_id','$key','$value','TXT','3600','NULL')");
=======
						mysql_query("INSERT INTO records (domain_id, name, content, type, ttl) VALUES ($this_domain_id,'$key','$value','TXT',$ttl)");

						break;
					case "srv":
						$key = $o->hostname;
						if($o->param === null) continue;	

						if ($key !== "__base__") {
							$key = "$key.$domainname";
						} else {
							$key = "$domainname";
						}
						$hostname = "_{$o->service}._{$o->proto}.{$key}";
						$priority = $o->priority;
						$weight = $o->weight == null || strlen($o->weight) == 0 ? 0 : $o->weight;
						$value = "{$weight} {$o->port} {$o->param}";
						mysql_query("INSERT INTO records (domain_id, name, content, type, ttl, prio) VALUES ($this_domain_id, '$hostname', '$value', 'SRV', $ttl, $priority)");
>>>>>>> upstream/dev

						break;
				}
			}
<<<<<<< HEAD
			
		}
			

	$this->dbClose();
=======
		}
	   $this->dbClose();
>>>>>>> upstream/dev
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
<<<<<<< HEAD

	function dosyncToSystemPost()
	{
		global $sgbl;

	}

}

=======
	function syncToSystem()
	{
		global $sgbl;
  	}

	function dosyncToSystem()
	{
		global $sgbl;
      $this->dbactionDelete();
      $this->dbactionAdd();
  	}
	function dosyncToSystemPost()
	{
		global $sgbl;
  	}

}
>>>>>>> upstream/dev
