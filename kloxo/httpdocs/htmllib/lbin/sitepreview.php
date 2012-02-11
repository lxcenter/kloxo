<?php 
chdir("../../");
include_once "htmllib/lib/include.php"; 


function parse_etc_mime()
{
	$list = lfile_trim("/etc/mime.types");
	foreach($list as $s) {
		if (!$s) {
			continue;
		}
		if ($s[0] === '#') {
			continue;
		}
		$s = trimSpaces($s);
		$s = explode(" ", $s);
		$type = array_shift($s);
		foreach($s as $ss) {
			$res[$ss] = $type;
		}
	}
	return $res;
}

$res = parse_etc_mime();


$request = $_SERVER['REQUEST_URI'];

if (!csa($request, "sitepreview/")) {
	header("HTTP/1.0 404 Not Found");
	print("404--- <br> ");
	exit;
}

$request = strfrom($request, "sitepreview/");

$domain = strtilfirst($request, "/");

dprint($domain);
$sq = new Sqlite(null, 'web');
$res = $sq->getRowsWhere("nname = '$domain'");

if (!$res) {
	print("Domain Doesn't exist\n");
	exit;
}

$server = $res[0]['syncserver'];
$ip = getOneIPForServer($server);

rl_exec_get(null, 'localhost', 'addtoEtcHost', array($domain, $ip));
$file = curl_general_get("http://$request");

$pinfo = pathinfo($request);
$ext = $pinfo['extension'];
if (isset($res[$ext]) && $res[$ext] !== 'text/html' && $res[$ext] !== 'text/css') {
	header("Content-Type  $res[$ext]");
	print($file);
	exit;
}




rl_exec_get(null, 'localhost', 'removeFromEtcHost', array($domain));

include "/usr/local/lxlabs/kloxo/httpdocs/lib/hn_urlrewrite_example/hn_urlrewrite.class.php";

$rewrite = new hn_urlrewrite();

$page = $rewrite->_rewrite_page($domain, $file);
print($page);



