<?php 
function get_plural($word)
{
	if ($word[strlen($word) - 1] === 't' and $word[strlen($word) - 2] === 'n') {
			$ret = "{$word}s";
	} else {
		$ret = "{$word}en";
	}
	return ucfirst($ret);
}

// This is an alternate get_plural, which has the all the plurals are defined in a file.
function get_plural_alternate($word)
{
	include_once "lang/nl/lang_plural.inc";

	if (isset($__plural_desc[$word])) {
		return $__plural_desc[$word];
	}

	return "{$word}en";
}


