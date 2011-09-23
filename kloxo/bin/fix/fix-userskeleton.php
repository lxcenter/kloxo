<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

if (isset($list['server'])) { $server = $list['server']; }
else { $server = 'localhost'; }

setDefaultPages();