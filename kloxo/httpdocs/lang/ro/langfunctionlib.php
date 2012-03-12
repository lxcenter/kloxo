<?php
/*
 *    HyperVM, Server Virtualization GUI for OpenVZ and Xen
 *
 *    Copyright (C) 2000-2009    LxLabs
 *    Copyright (C) 2009-2012    LxCenter
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function get_plural($word)
{
	if ($word[strlen($word) - 1] === 's') {
		$ret = "{$word}es";
	} else if ($word[strlen($word) - 1] === 'y') {
		if ($word[strlen($word) - 2] === 'e') {
			$ret = "{$word}s";
		} else {
			$ret = substr($word, 0, strlen($word) - 1) . "ies";
		}
	} else if ($word[strlen($word) - 1] === 'x') {
		$ret = substr($word, 0, strlen($word) - 1) . "xes";
	} else {
		$ret = "{$word}s";
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


