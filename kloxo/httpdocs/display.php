<?php
<<<<<<< HEAD
$v = 0;
include_once 'htmllib/coredisplaylib.php';
sleep($v);

//setcookie("XDEBUG_SESSION", "sess", time () +  36000);
//setcookie("XDEBUG_SESSION", "sess");
print_time("start");

display_init();
print_time("start", "Start");

//dprint($gbl->__c_object->username);
//$list = $gbl->__c_object->getList('domaintemplate');
//$gbl->__c_object->__parent_o = null;
//dprintr($gbl->__c_object->ls);
//dprintr($gbl->__c_object->priv);
display_exec();
echo '<br />';
=======

include_once 'htmllib/coredisplaylib.php';

print_time("start");
display_init();
print_time("start", "Start");
display_exec();
echo '<br />';

>>>>>>> upstream/dev
