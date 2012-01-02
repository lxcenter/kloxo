<?php
    if (preg_match("/Mozilla\/\d.+Compatible; MSIE/i", $_SERVER['HTTP_USER_AGENT']) && !preg_match("/Opera/i", $_SERVER['HTTP_USER_AGENT'])) {
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    } else {
        header('Expires: 0');
        header('Pragma: no-cache');
    }

include("common.php");

$user = $_REQUEST['username'];
$id = $_REQUEST['id'];
$tijd = microtime(true);
$tijd = md5($tijd);

$cmd = $_REQUEST['cmnd'];
$command = "";

$link = lsqlite_open($g_db_file);
$res = sqlite_query($link, "SELECT * from channel where user = '$user' AND id = '$id'");
$chanlist = sqlite_fetch_array($res);
$channel = $chanlist['channel'];

$cmdlist = preg_split("/\n/", $cmd);
$cmdlist = array_reverse($cmdlist);
//$cmdlist = preg_split("/\n/", $cmd);
// Remove irc commands...



foreach($cmdlist as $cmd) {
	$cmd = preg_replace("/^\//", "", $cmd);
	if (preg_match("/^\/(.+)/", $cmd, $matches)) {
		if (preg_match("/^MSG ([^\s]+) (.+)/i", $matches[1], $match)) {
			$command = "PRIVMSG $match[1] :$match[2]";

		} elseif (preg_match("/^NOTICE ([^\s]+) (.+)/i", $matches[1], $match)) {
			$command = "NOTICE $match[1] :$match[2]";

		} elseif (preg_match("/^QUIT(.*)/i", $matches[1], $match)) {
			if ($match[1]) {
				$match[1] = ltrim($match[1]);
				$match[1] = " :$match[1]";
			}
			$command = "QUIT$match[1]";

		} elseif (preg_match("/^ME (.+)/i", $matches[1], $match)) {
		$command = "PRIVMSG $channel :\001ACTION $match[1]\001";

		} elseif (preg_match("/^NAMES(.*)/i", $matches[1], $match)) {
			if ($match[1]) {
				$command = "NAMES$match[1]";
			} else {
				$command = "NAMES $channel";
			}
		} elseif (preg_match("/^RAW (.+)/i", $matches[1], $match)) {
			$command = "$match[1]";

		} elseif (preg_match("/^NICK (.+)/i", $matches[1], $match)) {
			$command = "NICK $match[1]";

		} elseif (preg_match("/^WHOIS (.+)/i", $matches[1], $match)) {
			$command = "WHOIS $match[1]";

		} elseif (preg_match("/^CTCP ([^\s]+) (.+)/i", $matches[1], $match)) {
			$command = "PRIVMSG $match[1] :\001$match[2]\001";
		}
	} else { $command = "PRIVMSG $channel :$cmd"; }
	$command = preg_replace("/%C(\d+)/", "\003$1", $command);

	$bgcolor = "#00FF00";

	if ($command && $user && $id) { sqlite_query($link, "INSERT INTO phpchat VALUES('$user', '$id', '$tijd', '$command')"); }
}

/*
echo <<<EOF
<html>
<head><title>passed command</title>
</head><body bgcolor="$bgcolor"></body></html>
EOF;
*/
