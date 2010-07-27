<?php

include_once 'htmllib/lib/displayinclude.php';

function clearsession_main()
{
    global $gbl, $sgbl, $login, $ghtml;
    initProgramlib('admin');
    $login->__session_timeout = true;

    $ulist = $login->getList('utmp');
    if(!empty($ulist))
    {
        foreach($ulist as $u) {
            if ($u->timeout < time()) {
                $u->setUpdateSubaction('');
                $u->logouttime = time();
                $u->logoutreason = 'Session Expired';
                $u->write();
            }
        }
    }

    $slist = $login->getList("ssessionlist");
    if(!empty($slist))
    {
        foreach($slist as $s) {
            if ($s->timeout < time()) {
                $s->dbaction = 'delete';
                $s->write();
            }
        }
    }
}

clearsession_main();

// I do not want to wait for 600 secs in debug mode :)
// Altho why is there a sleep at all....
if ($sgbl->dbg >= 0) {
dprint("Sleeping for 10 seconds....\n");
sleep(10);
} else {
dprint("Sleeping for 600 seconds....\n");
sleep(600);
}
