<?php

include("common.php");
echo <<<EOF
<HTML>
<HEAD>
<TITLE>Nicklist</TITLE>
$css
</HEAD>
<BODY bgcolor="$chan_bg" text="$chan_fg" link="$chan_fg">

EOF;

if (isset($_REQUEST['list'])) {
	$list = $_REQUEST['list'];
} else {
	$list = "";
}
if ($list) {
    $nicknames = split(":", $list);
    foreach($nicknames as $n) {
        if (strlen($n) > 0) {
            echo "$n<BR />";
        }
    }
} else {
    echo "<br> Please wait..";
}

echo "</BODY>\n</HTML>\n";

