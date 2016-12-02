<?php 
function get_plural($word)
{
	if ($word[strlen($word) - 1] === '') {
		$ret = "{$word}";
	} else if ($word[strlen($word) - 1] === '') {
		if ($word[strlen($word) - 2] === '') {
			$ret = "{$word}τα";
		} else {
			$ret = substr($word, 0, strlen($word) - 1) . "";
		}
	} else if ($word[strlen($word) - 1] === '') {
		$ret = substr($word, 0, strlen($word) - 1) . "";
	} else {
		$ret = "{$word}";
	}
	return ucfirst($ret);
}

// This is an alternate get_plural, which has the all the plurals are defined in a file.
function get_plural_alternate($word)
{
	include_once "lang/en/lang_plural.inc";

	if (isset($__plural_desc[$word])) {
		return $__plural_desc[$word];
	}

	return "{$word}s";
}


