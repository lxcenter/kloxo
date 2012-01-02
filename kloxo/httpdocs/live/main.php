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
include("ircfunc.php");

$gl_never_responded = 1;
$gl_client_started = 0;
$livetrfp = null;
if ($g_login) {
	$livetrfp = lfopen("__path_program_etc/livetranscript.txt", "a");
}
/*
$channel = $_GET['channel'];
echo "$channel <br>";
if (!$channel) {
    $channel = $channels[0];
}
*/


if ($redirect) 
	$channel  = "{$chanbase}-command";
else
	$channel = $chanbase;


$licid = $_GET['licid'];

$user = $_GET['username'];
$id = $_GET['id'];

$base_nick = $_GET['username'];
//$nick = "hello";
//$serv_addr = "127.0.0.1";
//$serv_port = 6667;

echo <<<EOF
<html>
<head>
<title>PHP IRC Chat output page</title>
$css
</head>
<body bgcolor="$chan_bg" text="$chan_fg">

EOF;
flush();
$smily_code = array("/:-?\)/", 
		    "/;b/", 
		    "/:-?P/i", 
		    "/;-?\)/i", 
		    "/:-?\(/", 
		    "/:-?D/i",
		    "/:-?\|/i",
		    "/\(y\)/i",
		    "/:-?S/i");
$smily_repl = array("<img src='pics/sm-smile.gif' width=15 height=15>",
		    "<img src='pics/sm-bsmile.gif' width=15 height=15>",
		    "<img src='pics/sm-psmile.gif' width=15 height=15>",
		    "<img src='pics/sm-wink.gif' width=15 height=15>",
		    "<img src='pics/sm-sad1.gif' width=15 height=15>",
		    "<img src='pics/sm-dsmile.gif' width=15 height=15>",
		    "<img src='pics/sm-amazed.gif' width=15 height=15>",
		    "<img src='pics/sm-thumbsup.gif' width=15 height=15>",
		    "<img src='pics/sm-sad2.gif' width=15 height=15>");

@set_time_limit(3600); // ten minutes execution time, when user says or does something, this is reset with 10 min.
/* ** ** set_time_limit() doesn't seem to have any effect whatsoever... ** ** */

register_shutdown_function("einde");

echo <<<EOF
<script language="JavaScript"><!--; parent.scrl(1); //--></script>
EOF;


$socket = irc_open($serv_addr, $serv_port, $errno, $errstr);

if ($socket < 0) { echo "failed... $errno: $errstr"; return; }
else { echo "Connecting to Lxlabs Live. Please Wait....<br> "; }


$max_per_personnel = $_REQUEST['max_per_personnel'];
$max_number_personnel = $_REQUEST['max_number_personnel'];
$per_personnel = 1;
$number_personnel = 1;
if ($redirect)  {
	$nick = "{$base_nick}_{$number_personnel}-{$per_personnel}";
} else {
	$nick = $base_nick;
}

echo "<br>";
echo "<script language='JavaScript'>\n<!--;\n parent.scrl(1);\n //-->\n</script>";
flush(); //output this;

$out = "";
$login_flag = 0;
$loggedin = false;
$nickcount = 0;
$inchan = false;
$nicktry = 0;
$signontime = time();
function einde() {
	global $link, $user, $id;
    global $socket,$signontime;
    if ($socket) {
		irc_write($socket, "QUIT :lxlabs \r\n");
		irc_close($socket);
    }
    sqlite_query($link, "DELETE FROM phpchat WHERE username = '$user' AND id = '$id'");
    $signofftime = time();
    $onlinetime = $signofftime-$signontime;

    $d1 = (floor($onlinetime/3600) < 10) ? "0".floor($onlinetime/3600) : floor($onlinetime/3600);
    $rest = $onlinetime%3600;
    $d1 .= (floor($rest/60) < 10) ? ":0".floor($rest/60) : ":".floor($rest/60);;
    $rest = $rest%60;
    $d1 .= ($rest < 10) ? ":0".$rest : ":".$rest;
    echo "Signed on at: " . date("H:i:s d-m-Y", $signontime) . ", Signed off at: " . date("H:i:s d-m-Y", $signofftime) . "<br>";
    echo "Online time: $d1 ($onlinetime seconds)";
}


function retn_color($fg, $bg) {
    global $ircColors;
    if ($bg != -1) {
    	return "<font style='color: ".$ircColors[$fg]."; background-color: ".$ircColors[$bg].";'>";
    } else {
	return "<font style='color: ".$ircColors[$fg].";'>";
    }
}
function smile_repl($string) {
	return $string;
    global $smily_code, $smily_repl, $ircColors, $page_bg, $page_fg;
    $string = preg_replace("/\003(\d+),(\d+)/e", "retn_color($1,$2)", $string);
    $string = preg_replace("/\003(\d+)/e", "retn_color($1, -1)", $string);
    $string = preg_replace("/\003/", "<font style='color: $page_fg; background-color: $page_bg;'>", $string);
    for ($a = 0; $a < substr_count($string, "<font"); $a++) {
	$string .= "</font>";
    }
    return preg_replace($smily_code, $smily_repl, $string);    
}

$link = lsqlite_open($g_db_file);

$fully_logged_in = 0;
$timetoping = 10; // every tenth loop ask a ping reply
$gl_timer = 0;
$gl_nonavailable_printed = false;
while($socket > 0) {

	$read = null;
	$read[0] = $socket;
	$writea = null;
	$excpta = null;
	/*
	foreach((array) $client as $c) {
		$read[] = $c['sock'];
}
*/
	//dprint("Before: ");
		//dprintr($read);
		// Set up a blocking call to stream_select()
	$ready = stream_select($read, $writea, $excpta, 1);

	//dprint("After: $ready");
		//dprintr($read);

		// This means that sock - which is our main master socket - is ready for reading, which in turn signifies that a NEW connection has arrived. The other members of the read array 
	$full = null;
	if ($ready) {
		if (in_array($socket, $read)) {
			$full = irc_read($socket, 4096);
		}
	}
	$full = rtrim($full);

	$timeout = 30;

	if ($gl_never_responded && $gl_client_started) {
		$gl_timer++;
		if ($gl_timer == $timeout) {
			irc_write($socket, "PRIVMSG $channel :lxclmsg2 No response for 50 sec\r\n");
			//print($gl_timer);
		}
		if ($gl_timer % (2 * $timeout) == 0) {
			//print($gl_timer);
			irc_write($socket, "PRIVMSG $channel :lxclmsg3 No response for $gl_timer/20 sec\r\n");
		}
		if ($gl_timer % (6 * $timeout) == 0) {
			//print($gl_timer);
			irc_write($socket, "PRIVMSG $channel :lxclmsg3 No response for $gl_timer/20 sec\r\n");
			if (!$gl_nonavailable_printed) {
				print("It appears there's no one in the channel. Please leave your message here, or please contact lxhelp@lxlabs.com. We apologize for the inconvenience <br> \n");
				$gl_nonavailable_printed = true;
			} else {
				print("...");
			}
		}
	}

	$list = explode("\n", $full);
	foreach($list as $out) {
	if (strlen($out) > 1) {
		//print($out . "<br> ");
		if (preg_match("/PING (.+)/", $out, $matches)) {
			irc_write($socket, "PONG $matches[1]\r\n");
			//echo "ping-pong<br>";

		} elseif (preg_match("/:([^\s]+) NOTICE ([^\s]+) :(.+)/", $out, $matches)) {
			if (preg_match("/$nick/i", $matches[3])) { 
				$matches[1] = "<b>$matches[1]</b>"; 
			}
			$src = $matches[1];
			$text = smile_repl(htmlspecialchars($matches[3]));
			if (preg_match("/([^!]+)!.+/", $src, $matches)) {
				$src = $matches[1]; 
			}
			//echo "<font color='$ircColors[5]'>-$src- $text</font><br>";

		} elseif (preg_match("/:[^ ]* (\d+) ([^\s]+) (.+)/i", $out, $matches)) {

			if ($matches[1] == "006" || $matches[1] == "001") {
				if ($redirect) {
					$cmdchan = "{$chanbase}_{$number_personnel}-command" ;
					$realchan = $chanbase . "_" . $number_personnel . "-" . $per_personnel ;
					irc_write($socket, "JOIN #$cmdchan\r\n");
					irc_write($socket, "PRIVMSG #$cmdchan :lxcommand channel #$realchan\r\n");
					irc_write($socket, "PART #$cmdchan\r\n");
					//print($realchan);
					irc_write($socket, "JOIN #$realchan\r\n");
				} else {
					irc_write($socket, "JOIN #$chanbase\r\n");
				}
				//print("gotcha<br> \n");
			}
			if ($matches[1] == "PONG") {
			}

			if ($matches[1] == "376") { 
				/*end of motd*/ $loggedin = true; 
			}

			elseif($matches[1] == "433") {
				//print($out);
				if ($redirect) {
					if ($number_personnel == $max_number_personnel) {
						$number_personnel = 1;
						if ($per_personnel == $max_per_personnel) {
							$per_personnel  = 1;
							$number_personnel = 1;
							print("All Personnel currently busy. Will try after 30 seconds.... <br> \n");
							flush();
							sleep(30);
						} else {
							$per_personnel++;
						}
					} else {
						$number_personnel++;
					}
					$nick = "{$base_nick}_{$number_personnel}-{$per_personnel}";
				} else {
				
					$nickcount++;
					$nick = $base_nick . "$nickcount";
				}
				//echo "<font color='$ircColors[7]'>Nick already in use, changing to: $nick</font><br>\n";
				irc_write($socket, "NICK :$nick\r\n");
			}


			elseif ($matches[1] == "422") { 
				/* no motd, but logged in */ $loggedin = true; 
			}
			elseif ($matches[1] == "353") { //names
				if (preg_match("/= (\#[^\s]+) :(.+)/", $matches[3], $match)) {
					$namen = $match[2];
					//$namen = str_replace("@", "", $match[2]);
					//$namen = str_replace("%", "", $namen);
					//$namen = str_replace("+", "", $namen);
					$names = preg_split("/\s+/", $namen);
					natcasesort($names);
					if (!isset($nicklist["$match[1]"])) {
						$nicklist["$match[1]"]  = "";
					}
					foreach($names as $name) {
						//echo "$name, ";
						$name = str_replace("@", "", $name);
						if ($redirect) {
							if ($name == $nick) {
								$name = "Myself";
							}
						}
						$nicklist["$match[1]"] .= "$name:";
					}

					
				}

			} elseif ($matches[1] == "366") { // endofnames
				if (preg_match("/(#[^\s]+)/", $matches[3], $match)) {

					if (isset($nicklist[$match[1]])) {
						$namelist = $nicklist[$match[1]];
						echo "\n<script language='JavaScript'>\n<!--;\n\nparent.nixreload(':$namelist');\n\n//-->\n</script>\n\n";
					}
					$nicklist[$match[1]] = "";
					$_tnlist = explode(":", $namelist);

					echo "</font>";
					if (!$fully_logged_in) {

						if ($licid !== 'nobody') {
							echo "Hello $licid, ";
						}

						echo "please type your message to start the conference <br> \n";
						$fully_logged_in = 1;
					}
					flush();
				}
			} elseif ($matches[1] == "332") {
				if (preg_match("/(#[^\s]+) :(.+)/", $matches[3], $match)) {
					//echo "<font color='$ircColors[7]'>--- Topic for $match[1] is: $match[2]</font><br>";
				}
			} elseif (($matches[1] == "372" || $matches[1] == "375") && $hide_motd) {
				// do nothing, the motd doesn't have to be displayed...
			} elseif ($matches[1] == "317") {
				// whois idle time and signon time
				if (preg_match("/([^\s]+)\s+(\d+)\s+(\d+)\s+:.+/", $matches[3], $match)) {
					$d1 = (floor($match[2]/3600) < 10) ? "0".floor($match[2]/3600) : floor($match[2]/3600);
					$rest = $match[2]%3600;
					$d1 .= (floor($rest/60) < 10) ? ":0".floor($rest/60) : ":".floor($rest/60);;
					$rest = $rest%60;
					$d1 .= ($rest < 10) ? ":0".$rest : ":".$rest;
					$d2 = date("Y-m-d H:i:s", $match[3]);
					//echo "<font color='$ircColors[12]'>-$serv_name- $match[1] idle: $d1, signon: $d2</font><br>";
				}
			} else {
				//echo "<font color='$ircColors[12]'>-$serv_name- $matches[3]</font><br>";
			}

		} elseif (preg_match("/Closing Link(.*)/i", $out, $matches)) {
			//echo "<font color='$ircColors[4]'>Disconnected$matches[1]...</font><br>\n";
			irc_close($socket);
			sleep(60);
			$socket = irc_open($serv_addr, $serv_port, $errno, $errstr);

			if ($socket < 0) { echo "failed... $errno: $errstr"; return; }
			else { echo "Connecting Again....<br> "; }

			$login_flag = 0;
			$loggedin = false;
			$inchan = false;
			$nicktry = 0;
			flush();
			continue;

		} elseif (preg_match("/:([^!]+)![^\s]+ PRIVMSG ([^\s]+) :(.+)/", $out, $matches)) {
			if (preg_match("/$nick/i", $matches[3])) { $matches[1] = "<b>$matches[1]</b>"; }
			$matches[3] = smile_repl(htmlspecialchars($matches[3]));

			if (preg_match("/\001(\w+)(.*)/i", $matches[3], $match)) { // CTCP's
				if ($match[1] == "VERSION") {
					irc_write($socket, "NOTICE $matches[1] :\001VERSION \r\n");
				} elseif ($match[1] == "PING") {
					irc_write($socket, "NOTICE $matches[1] :\001PING$match[2]\r\n");
				} elseif ($match[1] == "CLIENTINFO") {
					irc_write($socket, "NOTICE $matches[1] :\001CLIENTINFO ip: {$_SERVER['REMOTE_ADDR']} ; {$HTTP_SERVER_VARS['REMOTE_HOST']}\001\r\n");
					irc_write($socket, "NOTICE $matches[1] :\001CLIENTINFO useragent: {$_SERVER['HTTP_USER_AGENT']}\001\r\n");
				} elseif ($match[1] == "ACTION") {
					$matches[3] = substr($matches[3],7);
					echo "<font color='$ircColors[6]'>* $matches[1] $matches[3]</font><br>";
				} elseif ($match[1] == "DCC") {
					preg_match("/^[\W]*(\w+)\s+([^\s]+)\s+\d+\s+\d+[\s\d]*/", "$match[2]", $blaat);
					//echo "<font color='$ircColors[5]'>-- Ignored DCC from $matches[1] ($blaat[1] $blaat[2])</font><br>";
					//irc_write($socket, "NOTICE $matches[1] :Sorry, but my client (PHPWebchat) doesn't support DCC transfers.\r\n");
				} else {
					echo "CTCP: $matches[3]<br>";
				}
			} elseif (!preg_match("/^#.+/", $matches[2])) {
				echo "&lt;<font color='$ircColors[7]'>$matches[1]-&gt;$nick</font>&gt; $matches[3]<br>";
			} else {
				
				$msg = preg_replace("/\s+/", " ", $matches[3]);
				$msglist = explode(" ", $msg);
				if ($msglist[0] == "lxbuzz") 
					continue;

				$msg = "";
				if (isset($msglist[0]) && $msglist[0] == "lxquit") {
					einde();
					exit(0);
				}
				foreach($msglist as $m) {
					$msg .= " " . $m;
				}
				if ($matches[1] == $nick) {
					print("<font color=blue>&lt;$matches[1]&gt; </font> ");
				} else {
					print("<font color=black>&lt;$matches[1]&gt; </font> ");
				}
				print(" $msg<br>");
				if ($livetrfp) {
					fwrite($livetrfp, "$matches[1]: $msg\n");
				}

				$gl_never_responded = 0;

			}

		} elseif (preg_match("/:([^!]+)![^\s]+ NICK :(.+)/", $out, $matches)) {
			if ($nick == $matches[1]) {
				echo "<font color='$ircColors[3]'>-=- You are now known as $matches[2]</font><br>";
			} else {
				echo "<font color='$ircColors[3]'>-=- $matches[1] is now known as $matches[2]</font><br>";
			}
			irc_write($socket, "NAMES $matches[2]\r\n");

		} elseif (preg_match("/:([^!]+)![^\s]+ JOIN :(.+)/", $out, $matches)) {
			//echo "<font color='$ircColors[3]'>--&gt; $matches[1] has joined  the channel</font><br>";
			$channel = $matches[2];
			$res = sqlite_query($link, "delete from channel where user = '$user' and id = '$id';");
			$res = sqlite_query($link, "insert into channel (user, id, channel) values ('$user', '$id', '$channel');");
			$inchan = true;
			/*
			if ($matches[1] != $nick) { 
				irc_write($socket, "NAMES $matches[2]\r\n"); 
			}
		*/
			irc_write($socket, "NAMES $matches[2]\r\n"); 

		} elseif (preg_match("/:([^!]+)![^\s]+ PART (.+)/", $out, $matches)) {
			if (preg_match("/(#[^\s]+) :(.+)/", $matches[2], $match)) {
				//echo "<font color='$ircColors[3]'>&lt;-- $matches[1] has left $match[1] ($match[2])</font><br>";
			} else {
				//echo "<font color='$ircColors[3]'>&lt;-- $matches[1] has left $matches[2]</font><br>";
			}
			irc_write($socket, "NAMES $matches[2]\r\n");

		} elseif (preg_match("/:([^!]+)![^\s]+ QUIT :(.*)/", $out, $matches)) {
			//echo "<font color='$ircColors[3]'>&lt;-- $matches[1] has left</font><br>";
			irc_write($socket, "NAMES #$channel\r\n");

		} elseif (preg_match("/:([^!]+)![^\s]+ TOPIC (#[^\s]+) :(.*)/", $out, $matches)) {
			//echo "<font color='$ircColors[7]'>--- $matches[1] changed the topic for $matches[2] to: $matches[3]</font><br>";

		} elseif (preg_match("/:([^!]+)![^\s]+ MODE (#[^\s]+) ([^\s]+) (.+)/", $out, $matches)) {
			//echo "<font color='$ircColors[7]'>--- $matches[1] sets mode $matches[3] on $matches[4]</font><br>";
			irc_write($socket, "NAMES $matches[2]\r\n");

		} elseif (preg_match("/:([^\s]+) MODE ([^\s]+) :(.+)/", $out, $matches)) {
			//echo "<font color='$ircColors[7]'>--- $matches[1] sets mode $matches[3] on $matches[2]</font><br>";

		} else {
			//echo "$out<br>";
		}
		echo "\n";

	}
	}
    /**********************************************************************************************/





    $result = sqlite_query("SELECT * FROM phpchat WHERE username = '$user' AND id = '$id' ORDER BY tijd", $link);
	$a = 0;
	while ($rij = sqlite_fetch_array($result, SQLITE_ASSOC)) {
		$a++;
		$var = "DELETE FROM phpchat WHERE username = '$user' AND id = '$id' AND tijd = '{$rij['tijd']}'";
		sqlite_query($link, $var);
		if (preg_match("/(PRIVMSG) ([^\s]+) :(.+)/i", $rij['commando'], $match) || preg_match("/(NOTICE) ([^\s]+) :(.+)/i", $rij['commando'], $match)) {
			$match[3] = smile_repl($match[3]);
			if ($match[1] == "PRIVMSG") {
				if (preg_match("/\001ACTION ([^\001]+)\001/i", $match[3], $mat)) {
					echo "<font color='$ircColors[6]'>* $nick $mat[1]</font><br>";
				} elseif (preg_match("/\001(.+)\001/", $match[3], $mat)) { // CTCPs
					echo "<font color='$ircColors[5]'>CTCP $match[2] $mat[1]</font><br>";
					
				} elseif (preg_match("/^#.+/", $match[2])) { // The main client conversation
					if ($redirect) {
						echo "<font color=blue>&lt;Myself&gt; $match[3] </font> <br>";
						if ($livetrfp) {
							fwrite($livetrfp, "Myself: $match[3]\n");
						}
					} else {
						echo "<font color=blue>&lt;$nick&gt; $match[3] </font> <br>";
					}
					if (!$gl_client_started) {
						$gl_client_started = 1;
						irc_write($socket, "PRIVMSG $channel :lxclmsg1 Client $licid has started talking \r\n");
						print("Connecting to the chat personnel... Please wait <br> \n");
					}
				} else {
					echo "&lt;<font color='$ircColors[7]'>$nick-&gt;$match[2]</font>&gt; $match[3]<br>";
				}
			} elseif ($match[1] == "NOTICE") {
				echo "<font color='$ircColors[5]'>&gt;$match[2]&lt; $match[3]</font><br>";
			} else {
				echo "<font color=blue>&lt;Myself&gt; $match[2] </font> <br>";
			}
		}
		if (preg_match("/^NICK (.+)/i", $rij['commando'], $match)) {
			$nick = $match[1];
			irc_write($socket, "NAMES $channel\r\n");
			flush();
		}
		irc_write($socket, "{$rij['commando']}\r\n");
		if (preg_match("/^QUIT.*/", $rij['commando'])) {
			echo "<font color='$ircColors[4]'>Disconnected...</font><br>";
			break 2;
			break 2;
		}
		echo "\n";
	}
	if ($a > 0) { @set_time_limit(3600); /* ten minutes extra to say sth */ }
    /**********************************************************************************************/
    if ($login_flag == 0) {
		$name = $_SERVER['REMOTE_ADDR'];
	    irc_write($socket, "USER phpchat {$_SERVER['REMOTE_ADDR']} $name :$name \r\nNICK :".$nick."\r\n");
	    $login_flag = 1;
    }
    if (connection_aborted()) {
		echo "<font color='$ircColors[4]'>Disconnected...</font><br>";
		break 2;
    }
	echo "<!-- -->"; // keep connection alive

	$timetoping--;
	if ($timetoping < 0) {
		$timetoping = 10;
		irc_write($socket, "PING LAG".time() . "\r\n");
	}
	flush_server_buffer();
	flush(); //output all...

//    sleep(1); // ony sleep() works on windoze apache :/
}
einde();

function getGreeter($nicklist, $nick)
{
	foreach($nicklist as $n) {
		$n = trim($n);
		if (!$n) {
			continue;
		}
		if ($n === $nick) {
			continue;
		}
		if ($n === 'Myself') {
			continue;
		}
		if (cse($n, "|aw")) {
			continue;
		}
		return "Connected...<br><br> &lt;&gt; Type your message to start the conference. <br>";
	}

	return "Connected...<br><br> &lt;Leave Message&gt; I am not available now. Please leave a message. <br>";


}
?>
</body>
</html>
