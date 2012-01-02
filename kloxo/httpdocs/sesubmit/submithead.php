<!--
    PHPSubmit - A search engine submission script
    Copyright (C) 2000 Matt Wilson

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
-->

<?php

require "engines.php";

$usinge = array();
$usingn = array();
$usingp = array();

for($t=0; $t<sizeof($engines); $t++){
	print "<!-- \$ue[$t] = ".$ue[$t]." -->\n";
	if($ue[$t] == 1){
		$usinge[] = $engines[$t];
		$usingn[] = $enginename[$t];
	}
}

echo "<!-- sizeof(\$usinge) = ".sizeof($usinge)." -->\n";
echo "<!-- sizeof(\$usingn) = ".sizeof($usingn)." -->\n";

for($t=0; $t<sizeof($uk); $t++){
	if($uk[$t] == 1)
		$usingp[] = $k[$t];
}

echo "<!-- sizeof(\$usingp) = ".sizeof($usingp)." -->\n";

if(!sizeof($usinge) || !sizeof($usingp)){
	die("<script style=javascript>top.location.href=\"noneselected.html\";</script>");
//	die("hmm, problem...");
}

$totaltime = ((sizeof($usinge)*sizeof($usingp))*4)+4;

function timeleft()
{
        global $totaltime;

        $seconds = $totaltime;

        $hours = floor($seconds/3600);
        $seconds -= $hours*3600;
        $minutes = floor($seconds/60);
        $seconds -= $minutes*60;

        if($hours)
                $ret = $hours." hours ";

        if($minutes)
                $ret = $ret.$minutes." minutes ";

        if($seconds)
                $ret = $ret.$seconds." seconds";

        $totaltime -= 4;

	if($ret == "")
		$ret = "Finished";

        return $ret;
}

?>

<html>
<head>
<script style=javascript>
	var totaltimeleft = "<?php echo timeleft(); ?>";

        function go(){
                document.topform.cl.value = 'Initialising, sec...';

<?php
	for($e = 0; $e < sizeof($usinge); $e++){
		for($p=0; $p<sizeof($usingp); $p++){
			echo "setTimeout(\"document.topform.cl.value='".$usingp[$p]."'; document.topform.ce.value='".$usingn[$e]."'; top.progress.location.href='".str_replace("[>URL<]",$usingp[$p],str_replace("[>EMAIL<]",$email,str_replace("[>KEYS<]", $keys, $usinge[$e])))."'; document.topform.ctt.value='".(($e*sizeof($usingp))+$p)."'; document.topform.ctr.value='".timeleft()."';\",".(4000*(($e*sizeof($usingp))+$p)).");\n";
		}
	}

	        echo "setTimeout(\"top.location.href='done.html';\",".((sizeof($usinge)*sizeof($usingp))*4000).");";
?>
	}
</script>
</head>

<body onload="document.topform.cl.value='Ready to start the submission process, Press Go! to begin'; document.topform.ctr.value=totaltimeleft; document.topform.startbtn.value='Go!';" bgcolor=#ffffff text=#000000>

<base target=progress>
<form name=topform>
<center><B><font face=Verdana size=2>
<table border=1 cellspacing=2 cellpadding=0 bgcolor=#a8fe9e>
<tr><td><b><font size=2>Current:</font></b></td><td><font size=2><input type=text name=cl size=65 value="Retrieving pages to be submitted..."></font></td></tr>
<tr><td><b><font size=2>Estimate time remaining:</font></b></td><td><font size=2><input type=text name=ctr size=65 value="Calculating time remaining..."></font></td></tr>
<tr><td><font size=2><b>Engine:</b></font></td><td><font size=2><input type=text name=ce size=12 value="Waiting...">&nbsp;&nbsp;&nbsp;Counter:<input type=text name=ctt size=5 value=0>&nbsp;&nbsp;<input type=text name=ct value=<?php echo (sizeof($usinge)*sizeof($usingp)); ?> size=5><input type=button value="Wait..." onClick="go()" name=startbtn>&nbsp;<input type=button value='Quit' onClick="top.location.href='phpsubmit.php';"></font></td></tr>
</table>
</form>
</center>
</body>
</html>
