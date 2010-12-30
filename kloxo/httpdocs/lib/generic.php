<?php 

function confirm($prompt, $default = null)
{

	print($prompt);
	$v = fread(STDIN, 100);

	print("\n");

	$v = strtolower($v);

	if (!$default) {
		if ($v === 'y' || $v === 'yes') {
			return true;
		}
		return false;
	}
	if ($v === 'n' || $v === 'no') {
		return false;
	}
	return true;
}


