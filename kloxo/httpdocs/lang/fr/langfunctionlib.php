<?php 
// Translation done by pob944
function get_plural($word)
{
	if ($word[strlen($word) - 1] === 's') {
		$ret = "{$word}es";
	} else if ($word[strlen($word) - 1] === 'y') {
		$ret = substr($word, 0, strlen($word) - 1) . "ies";
	} else {
		$ret = "{$word}s";
	}
	return ucfirst($ret);
}
