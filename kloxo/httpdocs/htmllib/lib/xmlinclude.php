<?php 


function parse_category($cat)
{
	foreach($cat->category as $v) {
		dprint("Category: $v\n");
	}
}
function parse_entry($entry)
{
	if (!$entry) { return ; }
	foreach($entry->entry as  $v) {
		dprintr("$v->label $v->path \n");
	}

}
function parse_requirement($requirement)
{
	$db = $requirement->children('http://apstandard.com/ns/1/db');
}

function aps_check_if_db($s)
{
	$rq = $s->requirements;

	$db = $rq->children('http://apstandard.com/ns/1/db');

	if ($db) { return true; }
	return false;
}



function parse_mapping($root, $m, $parent_path)
{
	$a = $m->children('http://apstandard.com/ns/1/php')->attributes();
	$spath = $m->attributes()->url;
	$parent_path = "$parent_path/$spath";

	if ($a) {
		dprintr($a);
		if ((string)$a->writable === 'true') {
			lxfile_generic_chmod("$root/$parent_path", "0775");
			dprint("$parent_path Is writable\n");
		}
	} else {
		dprint("$parent_path Not writable\n");
	}


	foreach($m->mapping as $mp) {
		parse_mapping($root, $mp, $parent_path);
	}

}
