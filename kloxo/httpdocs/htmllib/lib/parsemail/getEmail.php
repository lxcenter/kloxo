<?php
    // get an email from a post request and parse it using parseMail class
    // Version 0.1, 2005/03/16
    // Copyright (c) Frank Rust, TU Braunschweig (f.rust@tu-bs.de)
    //

    require("parseMail.php");
  
    // could be a bit more safety here:
    // get the contents of the uploaded email file    
    $mailtext=file_get_contents($_FILES['postfile']['tmp_name']);

    // parse that file
    $email=new parseMail($mailtext);

    // show the results (or do anything useful...)
    print_r($email);


?>