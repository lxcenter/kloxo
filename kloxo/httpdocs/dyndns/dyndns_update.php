<?php
/**
 * DynDns server for Kloxo
 * @author Lars Nordseth (LN)
 * @copyright Copyright (c) 2010, Lars Nordseth
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPL - GNU Affero General Public License
 * @package DynDns
 */
header("Content-type: text/plain");
require_once "../htmllib/lib/include.php"; 
initProgram('admin');

/**
 * DDnsRecordUpdateRequest
 * Class for storing a request to update a single hostname
 */
class DDnsRecordUpdateRequest
{
	/**
	 * Hostname to update
	 * @var string
	 */
	protected $hostname;
	/**
	 * IP-adress (a.b.c.d)
	 * @var string
	 */
	protected $ip;
	/**
	 * Username. This is stored per hostname, just in case any protocol supports it
	 * @var string
	 */
	protected $username;
	/**
	 * Password stored as plain text
	 * @var string
	 */
	protected $password;
	
	/**
	 * Ctor, initializes member variables
	 */
	public function __construct($username, $password, $hostname, $ip, $offline)
	{
		$this->username = $username;
		$this->password = $password;
		$this->hostname = $hostname;
		$this->ip = $ip;
		$this->offline = $offline;
	}
	// Getters
	public function getHostname()	{ return $this->hostname; }
	public function getIp()	{ return $this->ip; }
	public function getOffline()	{ return $this->offline; }
	public function getUsername()	{ return $this->username; }
	public function getPassword()	{ return $this->password; }
}

/**
 * DDnsRecordUpdateResult - constants for return codes
 * Using the return codes from dyndns2
 * Other implementations have to translate the return codes 
 * before returning them to the client.
 * (http://www.dyndns.com/developers/specs/return.html)
 */
class DDnsRecordUpdateResult
{
	const BADAUTH = 'badauth';
	const GOOD = 'good';
	const NOCHG = 'nochg'; // Not considered abusive by this implementation
	const NOTFQDN = 'notfqdn';
	const NOHOST = 'nohost';
	const NUMHOST = 'numhost';
	const ABUSE = 'abuse'; // Not used (for now)
	const BADAGENT = 'badagent'; // Ditto
	const GOOD_127_0_0_1 = 'good 127.0.0.1'; // Ditto
	const DNSERR = 'dnserr';
	const NINEONEONE = '911';
}

/**
 * DDnsServerBase
 * Base class for DDNS-updates in Kloxo.
 */
abstract class DDnsServerBase
{
	
	/**
	 * Change DNS for one hostname
	 * May match more than one record in Kloxo in case of round robin load balancing/failover
	 * @param string $hostname
	 * @param string $ip
	 * @param string $username
	 * @param string $password plain text password
	 * @offline string $offline "on" sets the record offline (removed from the driver, not from Kloxo)
	 */
	protected function updateKloxoDnsRecord($username, $password, $hostname, $ip, $offline)
	{
		// Get the leftmost part of the hostname
		$result = DDnsRecordUpdateResult::NOHOST;
		$parts = explode('.', $hostname);
		$rrName = array_shift($parts);
		$zoneName = implode('.', $parts);
		$zone = new Dns(null, 'localhost', $zoneName);
		if ($zone->dbaction != 'add') {
			$zone->get();
			if (isset($zone->dns_record_a)) {
				foreach ($zone->dns_record_a as $rr) {
					// Include username in the condition to make it possible to match unique round robin records
					if ($rr->ttype === 'ddns' && $rr->hostname === $rrName && isset($rr->user) && $rr->user === $username) {
						if ($rr->pwd === $password) {
							if ($rr->param === $ip && $rr->offline === $offline) {
								// Match, but no change. If already matched, the result is still GOOD
								if ($result != DDnsRecordUpdateResult::GOOD)
									$result = DDnsRecordUpdateResult::NOCHG;
							} else {
								$rr->param = $ip;
								$rr->offline = $offline;
								$rr->update_timestamp = time();
								$rr->update_from = $_SERVER['REMOTE_ADDR'];
								$rr->update_ua = $_SERVER['HTTP_USER_AGENT'];
								// Update Kloxo, capture output to avoid messing up reply to the DDNS client
								ob_start();
								$zone->setUpdateSubaction('full_update');
								$zone->was();
								$toDdnsLog = ob_get_contents(); // Should be logged...
								ob_end_clean(); // End of Kloxo-output
								$result = DDnsRecordUpdateResult::GOOD;
							}
						} else {
							// This is a bit fishy, if a hostname updates one record and fails to update another,
							// the result is still GOOD
							if ($result != DDnsRecordUpdateResult::GOOD)
								$result = DDnsRecordUpdateResult::BADAUTH;
						}
					}
				}
			}
		}
		return $result;
	}
	/**
	 * Change DNS for all hostnames.
	 * Each hostname may match two different records (in addition to any RR-duplicates): __base__.hostname and hostname
	 * Example: a.b.c (glue record in zone b.c) and __base__.a.b.c (base in sub zone)
	 * @param DDnsRecordUpdateRequest[] $requests
	 * @return string[2] Return codes, constants from DDnsRecordUpdateResult
	 */
	protected function updateKloxoDns($requests)
	{
		$results = array();
		foreach ($requests as $request) {
			$results[] = array(
				$this->updateKloxoDnsRecord($request->getUsername(), $request->getPassword(), $request->getHostname(), $request->getIp(), $request->getOffline()),
				$this->updateKloxoDnsRecord($request->getUsername(), $request->getPassword(), '__base__.' . $request->getHostname(), $request->getIp(), $request->getOffline())
			);
		}
		return $results;
	}
	/**
	 * Translate HTTP request to DDnsRecordUpdateRequest[]
	 * Parses superglobals to create DDnsRecordUpdateRequest objects
	 * @return DDnsRecordUpdateRequest[] Update requests, one for each hostname
	 */
	protected abstract function parseHttpRequest();
	/**
	 * Translate result codes to HTTP response
	 * @param string[] $results Result codes, constants from DDnsRecordUpdateResult
	 * @return void The result is sent to the client (ddclient, browser, etc.)
	 */
	protected abstract function createHttpResponse($results);
	/**
	 * Update
	 * @return void The result is sent to the client from createHttpResponse
	 */
	public function update()
	{
		$updateRequests = $this->parseHttpRequest();
		$updateResults = $this->updateKloxoDns($updateRequests);
		$this->createHttpResponse($updateResults);
	}
}

/**
 * DDnsServerDynDns2
 * Implementation of the dyndns2 protocol
 */
class DDnsServerDynDns2 extends DDnsServerBase
{
	/**
	 * Translate HTTP request to DDnsRecordUpdateRequest[]
	 * Implementation for dyndns2
	 * Parses superglobals to create DDnsRecordUpdateRequest objects
	 * @return DDnsRecordUpdateRequest[] Update requests, one for each hostname
	 */
	protected function parseHttpRequest()
	{
		// dyndns2 uses HTTP Basic Auth
		$username = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
		$password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
		if ($username == null || $password == null) {
			header("WWW-Authenticate: Basic realm=\"Kloxo DDNS Server (dyndns2)\"");
			exit;
		}
		// Hostname(s) as CSV
		$hostname = isset($_REQUEST['hostname']) ? strip_tags(mysql_real_escape_string(strtolower(trim($_REQUEST['hostname'])))) : null;
		if (!$hostname) {
			$this->createHttpResponse(DDnsRecordUpdateResult::NOTFQDN);
			exit;
		}
		// Hostname(s) as array
		$hostnames = explode(',', $hostname);
		if (count($hostnames) > 20) {
			$this->createHttpResponse(DDnsRecordUpdateResult::NUMHOST);
			exit;
		}
		// dyndns2 supports only a single IP, not IP per hostname
		$ip = isset($_REQUEST['myip']) ? trim($_REQUEST['myip']) : null;
		$ipOk = preg_match('/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?\.){3}25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]$/', $ip) == 1;
		if (!$ipOk) {
			$this->createHttpResponse(DDnsRecordUpdateResult::DNSERR); // Probably not the right code...
			exit;
		}
		$offline = (isset($_REQUEST['offline']) ? trim($_REQUEST['offline']) === 'yes' : false) ? 'on' : 'off';
		foreach ($hostnames as $hostname) {
			$updateRequests[] = new DDnsRecordUpdateRequest($username, $password, $hostname, $ip, $offline);
		}
		return $updateRequests;
	}
	/**
	 * Translate result codes to HTTP response
	 * Implementation for dyndns2
	 * @param string[][] $results Result codes, constants from DDnsRecordUpdateResult
	 * @return void The result is sent to the client (ddclient, browser, etc.)
	 */
	protected function createHttpResponse($results)
	{
		header('Content-type: text/html'); // dyndns2 says so
		if (is_array($results)) {
			foreach ($results as $result) {
				// Reduce two result codes per hostname to one
				if ($result[0] === DDnsRecordUpdateResult::GOOD || $result[1] === DDnsRecordUpdateResult::GOOD)
					echo DDnsRecordUpdateResult::GOOD;
				else
				if ($result[0] === DDnsRecordUpdateResult::NOHOST && $result[1] === DDnsRecordUpdateResult::NOHOST)
					echo DDnsRecordUpdateResult::NOHOST;
				else
				if ($result[0] === DDnsRecordUpdateResult::NOCHG || $result[1] === DDnsRecordUpdateResult::NOCHG)
					echo DDnsRecordUpdateResult::NOCHG;
				else
				if ($result[0] === DDnsRecordUpdateResult::BADAUTH || $result[1] === DDnsRecordUpdateResult::BADAUTH)
					echo DDnsRecordUpdateResult::BADAUTH;
				else
					echo $result[0]; // Just pick one...
				echo "\n";
			}
		} else {
			echo $results . "\n";
		}
	}
}

/**
 * Create server and update
 */
$server = new DDnsServerDynDns2();
$server->update();

