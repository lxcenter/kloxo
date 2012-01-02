<?php
require "engines.php";

$email = $_REQUEST['email'];
$keys = $_REQUEST['keys'];
$ue = $_REQUEST['ue'];
$k = $_REQUEST['k'];
$uk = $_REQUEST['uk'];


$location = "submithead.php?email=$email&keys=$keys";
for($p=0; $p<sizeof($k); $p++)
	$location .= "&k[".$p."]=".rawurlencode($k[$p]);

for($p=0; $p<sizeof($engines); $p++){
	$location .= "&ue[".$p."]=".$ue[$p];
}

for($p=0; $p<sizeof($uk); $p++){
	if($uk[$p] == 1){
		setcookie("uk[".$p."]", 1);
	} else {
		setcookie("uk[".$p."]", 0);
	}	
}

for($p=0; $p<sizeof($uk); $p++){
	$location .= "&uk[".$p."]=".$uk[$p];
}
for($l=0; $l<sizeof($engines); $l++){
	print "<!-- \$ue[$l] == ".$ue[$l]." -->\n";
}
?>

<html>
<head>
	<title>PHPSubmit is submitting your pages</title>
</head>

<frameset rows="150,*">
	<frame name="control" src="<?php echo $location; ?>" scrolling=auto>
<!--	<frame name="control" src="submithead.php3?email=<?php echo $email; ?>&sess_id=<?php echo $sess_id; ?>" scrolling auto>-->
	<frame name="progress" src=ready.html scrolling=auto>
</frameset>

</html>
