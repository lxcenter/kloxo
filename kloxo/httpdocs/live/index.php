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

$licid = "";

dprintr($_REQUEST);
if (isset($_REQUEST['identity'])) {
	$licid .= " {$_REQUEST['identity']}";
}


if ($g_login) {
	include_once "../htmllib/lib/include.php";
	initProgram();

	if ($sgbl->isLxlabsClient()) {
		if (!$login->isOn('livesupport_flag')) {
			print("No license for Live support\n");
			exit;
		} else {
			$licid = $lic->lic_livesupport_name;
		}
	} else {

		if (!$login->isAdmin()) {
			Print("Not Admin");
			exit(0);
		}
		$lic = $login->getObject('license')->licensecom_b;
		if ($lic->isOn('lic_livesupport_flag')) {
			$licid = $lic->lic_livesupport_name;
		} else {
			print("Couldn't find license for the live support\n");
			$ht = trim(`hostname`);
			dprint($ht);
			if ($ht !== 'self.lxlabs.com') {
				exit;
			}
		}
	}


}


if (!lxfile_real($g_db_file)) {
	$tlink = lsqlite_open($g_db_file);
	@sqlite_query($tlink, "drop table phpchat;");
	@sqlite_query($tlink, "drop table channel;");
	sqlite_query($tlink, "CREATE TABLE phpchat (username, id, tijd, commando);");
	sqlite_query($tlink, "CREATE TABLE channel (user, id, channel);");
	sqlite_close($tlink);
}

$list = file("http://lxlabs.com/live/channel/$chanbase.conf");
$extra_url = "";
foreach($list as $l) {
	$l = trim($l);
	$l = preg_replace("/\s+/", " ", $l);
	$v = explode(" ", $l);
	$extra_url .= "&$v[0]=$v[1]";
	$$v[0] = $v[1];
}

if ($redirect)
	$username = $chanbase;
else
	$username = $_REQUEST['nickname'];

$id = microtime(true);
$id = md5($id);

echo <<<EOF
<html>
<head>
<title>Lxlabs Live </title>
$css
<script language="JavaScript"><!--;

EOF;


$user = $username;

echo <<<EOF
user = "$user";
id = "$id";
channel = "%23$channel";

EOF;
?>

commandHist = new Array();
commandNr = 0;

function send(cmd) {
    var a = commandHist.unshift(cmd);

    
    if (a > 20) {
		commandHist.pop();
    }
    commandNr = 0;
    cmd = escape(cmd);
    self.passcmnd.location = "pcmnd.php?username="+user+"&id="+id+"&cmnd="+cmd+"&channel="+channel;
//    alert("hey");
}

function sendSingle()
{
    var cmd = document.input.command.value;
	cmd = cmd.replace(/'/g, "\"");
	//alert(cmd);
	send(cmd);
    document.input.command.value=""; // empty
}

function sendMultiLine()
{
	var cmd = document.getElementById('multiline').value;
	cmd = cmd.replace(/'/g, "\"");
	send(cmd);
	document.getElementById('multiline').value = '';
}



function choseColor(color) {
    document.input.command.value += "%C"+color;
}

function nixreload(namelist) {
    self.nicklist.location = "nicklist.php?list="+namelist;
}

interID = -1;

function scrollen() {
    self.out.scrollBy(0,25)
}
function scrl(what) {
    if (what == 1) {
	if (interID == -1) {
	    clearInterval(interID);
	    interID = -1;
	    interID = setInterval("scrollen()", 250); // scroll down om de 250 ms;
	}
    } else {
	clearInterval(interID);
	interID = -1;
    }
}

function do_MorN(type) {
    var a;
    var act = "/msg ";
    if (type == 2) { act = "/notice "; }
    a = document.nicks.nix.selectedIndex;
    nick = document.nicks.nix.options[a].value;
    nick = nick.replace(/[\@\+\%]/, "");
    document.input.command.value = act + nick + " ";
}
function displayCommand(relElem) {
    commandNr += relElem;
    if (commandNr < 0) { commandNr = commandHist.length-1; }
    if (commandNr >= commandHist.length) { commandNr = 0; }
    document.input.command.value = commandHist[commandNr];
}
//--></script>

</head>

<table cellspacing="1" cellpadding="0" bgcolor="<?php echo $table_border; ?>" width='600' height='500'>
<tr>
<td bgcolor="<?php echo $chan_bg; ?>" width=100% height=100%>
<table cellpadding=6 cellspacing=6 height=100% width=100%> <tr> <td >
<iframe frameborder="0" height="100%" width="100%" name="out" src="main.php?licid=<?php echo $licid ?>&username=<?php echo "$user&channel=$channel&id=$id$extra_url"; ?>" valign="bottom" marginwidth="0" marginheight="0">Sorry your browser doesn't support this :S</iframe>
</td> </tr> </table> 
</td>
<td bgcolor="<?php echo $chan_bg; ?>" width="150" valign="top" align="left">
<iframe frameborder="0" height="100%" width="150" name="nicklist" src="nicklist.php" valign="bottom" marginwidth="0" marginheight="0">Sorry your browser doesn't support this :S</iframe>
</td>
</tr>
<tr>
<td align="left" bgcolor="<?php echo $input_bg; ?>" height='60'>
<form name="input" onSubmit="sendSingle();return false;" style="margin:2pt; padding:2pt;">
<table> <tr> <td ><input type="text" id=command name="command" size="68" > </td> <td ><input type="button" value="Send" onClick="sendSingle()"> </td> </table> 
<b> MultiLine </b>  <textarea name=multiline id=multiline rows=4 cols=41></textarea><input type="button" value="Send" onClick="sendMultiLine()">

<iframe frameborder="0" height="0" width="0" src="pcmnd.php" name="passcmnd" marginwidth="0" marginheight="0"></iframe>
</form>
</td>
<td align="center" bgcolor="<?php echo $input_bg; ?>" valign="middle" height='30'>
<table width="100%" cellspacing="0" cellpadding="0"><tr><td align=center>
<b> Auto Scroll: </b>  <br> 
<input type="button" value="Stop" onClick="scrl(0)">
<input type="button" value="Start" onClick="scrl(1)">
<!-- color chooser table -->
</td>

</tr>
<tr>


<td align="right"> </td>

</tr></table>
</td>
</tr>
</table>

</body>


