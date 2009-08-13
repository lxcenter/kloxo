<?php 

class mailcontent__qmail extends LxDriverclass {

static function getMailContent($mailbox)
{

	list($user, $dom) = explode("@", $mailbox);

	$path = mmail__qmail::getDir($dom);

	$ret = null;
	$maildir = "$path/$user/Maildir/new";
	self::parseDir($ret, $maildir);
	$maildir = "$path/$user/Maildir/cur";
	self::parseDir($ret, $maildir);
	$maildir = "$path/$user/Maildir/.Spam/new";
	self::parseDir($ret, $maildir);
	$maildir = "$path/$user/Maildir/.Spam/cur";
	self::parseDir($ret, $maildir);


	return $ret;

}

static function parseDir(&$ret, $maildir)
{

	$list = lscandir_without_dot_or_underscore($maildir);
	foreach($list as $l) {
		$ret[] = self::mail_parse("$maildir/$l");
	}
}


static function mail_parse($file)
{
	$fp = fopen($file, "r");

	$ret['header'] = null;
	$name = str_replace(",", "_s_coma_s_", $file);
	$name = str_replace(":", "_s_colon_s_", $name);
	$ret['nname'] = $name;
	while (!feof($fp)) {
		$l = fgets($fp);
		if ($l === "\n") {
			fclose($fp);
			break;
		}

		if (csb($l, "From:")) {
			$ret['from'] = strfrom($l, "From:");
		}
		if (csb($l, "Subject:")) {
			$ret['subject'] = strfrom($l, "Subject:");
		}
		if (csb($l, "Date:")) {
			$ret['date'] = strfrom($l, "Date:");
		}

		$ret['location'] = basename(dirname(dirname(($file))));

		$ret['header'] .= $l;
	}

	if (!isset($ret['subject'])) {
		$ret['subject'] = '[no subject]';
	}
	return $ret;

}

}
