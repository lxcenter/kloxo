<?php 

include_once 'htmllib/lib/include.php'; 

class DjbDNS
{
	/**
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * @var $dns_users The users needed for DjbDNS service
	 */
	private static $dns_users = array('tinydns', 'dnslog', 'dnscache', 'axfrdns');
	
	/**
	* Create the users needed for init DjbDNS.
	*
	* The users needed are: tinydns, dnslog, dnscache, axfrdns
	*
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @return void
	*/
	public function __construct() 
	{
		self::createDjbDNSUsers();
		self::checkPaths();
		self::getIPs();
	}
	
	/**
	 * Create the users needed for init DjbDNS.
	 * 
	 * The users needed are: tinydns, dnslog, dnscache, axfrdns
	 * 
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @return void
	 */
	private static function createDjbDNSUsers()
	{
		foreach (self::$dns_users as $user) {
			// Create the user only if not exist previously
			if(!posix_getpwnam($user)) {
				$result = system('useradd ' . $user);
				
				if($result !== 0) {
					echo 'Error: Adding ' . $user . ' user failed.' . PHP_EOL;
					exit();
				}
				else {
					echo 'Added ' . $user . ' user.' . PHP_EOL;
				}
			}
		}
	}
	
	/**
	* Check if the config paths are init
	*
	* The config paths are:
	* 	- /var/tinydns
	*   - /var/axfrdns
	*
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @return void
	*/
	private static function checkPaths()
	{
		if(!file_exists('/var/tinydns')) {
			$result = system('tinydns-conf tinydns dnslog /var/tinydns 127.0.0.1');
			
			if($result !== 0) {
				echo 'Error: enabling /var/tinydns config.' . PHP_EOL;
				exit();
			}
			else {
				echo 'Enabling /var/tinydns config.' . PHP_EOL;
			}
		}
		
		if(!file_exists('/var/axfrdns')) {
			$result = system('axfrdns-conf axfrdns dnslog /var/axfrdns /var/tinydns 0.0.0.0');
			
			if($result !== 0) {
				echo 'Error: enabling /var/axfrdns config.' . PHP_EOL;
				exit();
			}
			else {
				echo 'Enabling /var/axfrdns config.' . PHP_EOL;
			}
		}
	}
	
	/**
	* Get the IPs availables.
	* 
	* Configure the IP for Djbdns and write properly configs.
	*
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @return void
	*/
	private static function getIPs()
	{
		echo 'Getting the list of IPs availables.' . PHP_EOL;
		
		$ip_address_list = os_get_allips();
		//var_dump($ip_address_list);
		
		if(empty($ip_address_list))
		{
			echo 'Error: no IPs availaibles configured.' . PHP_EOL;
			exit();
		}
		else
		{
			// If array merge as IP/IP/...
			if(is_array($ip_address_list))
			{
				$ip_address_list = implode('/', $ip_address_list);
			}
		
			if(!lfile_put_contents('/var/tinydns/env/IP', $ip_address_list)) {
				echo 'Error: could not write the IP ' . $ip_address_list . ' for tinydns file /var/tinydns/env/IP. ' . PHP_EOL;
				exit();
			}
		
			if (!file_exists('/var/dnscache')) {
				$result = system('dnscache-conf dnscache dnslog /var/dnscache 127.0.0.1');
				
				if($result !== 0) {
					echo 'Error: enabling /var/dnscache config.' . PHP_EOL;
					exit();
				}
				else {
					echo 'Enabling /var/dnscache config.' . PHP_EOL;
				}
			}
		
			if(!lfile_put_contents('/var/axfrdns/tcp', ':allow')) {
				echo 'Error: could not write the config on /var/axfrdns/tcp.' . PHP_EOL;
				exit();
			}
			
			$result = system('cd /var/axfrdns;  /usr/local/bin/tcprules tcp.cdb tcp.tmp < tcp');
			
			if(intval($result) !== 0) {
				var_dump($result);
				echo 'Error: enabling tcprules config.' . PHP_EOL;
				exit();
			}
			else {
				echo 'Enabling tcprules config.' . PHP_EOL;
			}
			
			if(!lfile_put_contents('/var/dnscache/env/IP', '127.0.0.1')) {
				echo 'Error: could not write the localhost IP on /var/dnscache/env/IP.' . PHP_EOL;
				exit();
			}
		}
	}
}

$djbdns = new DjbDNS();