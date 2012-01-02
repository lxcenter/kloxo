<?php
    // Very simple example:
    // receive an email file on stdin and post that file to an url
    // (e.g. on a Unix-like server create an entry in /etc/aliases:
    // phpmail: |PostNewMail.php
    // PostNewMail.php should be executable and have the 
    // #!/path/to/console-php -q 
    // in the first line. Don't forget to allow
    // the execution of this program to smrsh)
    //
    // Version 0.1, 2005/03/13
    // Copyright (c) Frank Rust, TU Braunschweig (f.rust@tu-bs.de)
    //
    // This code is free software; you can redistribute it and/or modify
    // it under the terms of the GNU General Public License as published by
    // the Free Software Foundation; either version 2 of the License, or
    // (at your option) any later version.
    // 
    // This code is distributed in the hope that it will be useful,
    // but WITHOUT ANY WARRANTY; without even the implied warranty of
    // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    // GNU General Public License for more details.
    // 
    // Since this is a very short Program the GNU General Public License
    // is not included. Please find it on the website of the Open Software
    // Foundation at
    //     http://www.fsf.org/licensing/licenses/lgpl.txt
    // or write to the Free Software Foundation, Inc., 59 Temple Place, 
    // Suite 330, Boston, MA  02111-1307  USA
    

	
	// create temporary file to store the mail
	$tmpnam=tempnam("/tmp","MAIL");
	$tmpfile=fopen($tmpnam,"w");

	// read mail from stdin
	$input=fopen("php://stdin","r");
    
	// read complete mail and write it to tmpfile
	while (!feof($input)) {
		$line = fgets($input,4096);
		fwrite($tmpfile,$line);
	}
	fclose($input); 
	fclose($tmpfile);

	// use cURL to post the file to the Websystem
	$postvars=array("postfile" => "@".$tmpnam );
    
	$URL="http://www.mywebhost.org/getEmail.php";
    
	$ch=curl_init($URL);
	$user_agent="Franks Email POSTer";
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 0); // 1 for debugging
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$x=curl_exec($ch);
	$info=curl_getinfo($ch);
	curl_close($ch);

	//for debugging the console program: 
	echo $x; print_r($info);

	// cleanup
	unlink($tmpnam);	
?>