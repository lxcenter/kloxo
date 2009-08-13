<?php 

include_once "htmllib/lib/include.php"; 

$path = "__path_mail_root/domains/";

delete_domain_spam($path);

function delete_domain_spam($path)
{
	$domlist = lscandir_without_dot($path);

	foreach($domlist as $d) {
		if (!lis_dir("$path/$d/")) { continue; }

		if (is_numeric($d)) {
			delete_domain_spam("$path/$d");
			continue;
		}

		$aclist = lscandir_without_dot("$path/$d");
		foreach($aclist as $c) {

			if (!lis_dir("$path/$d/$c")) { continue; }

			$spamfolder = "$path/$d/$c/Maildir/.Spam/new";
			if (!lis_dir($spamfolder)) { continue; }
			print("Deleting Old spam from $spamfolder\n");
			remove_if_older_than_a_day_dir($spamfolder, 30);
		}
	}
}
