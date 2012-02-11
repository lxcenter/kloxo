<?php 

include_once "htmllib/lib/displayinclude.php";

initProgram('admin');

$gen = $login->getObject('general');

$gen->portconfig_b->sslport = null;
$gen->portconfig_b->nonsslport = null;
$gen->portconfig_b->nonsslportdisable_flag = null;
$gen->portconfig_b->redirectnonssl_flag = null;

$gen->setUpdateSubaction();
$gen->write();
